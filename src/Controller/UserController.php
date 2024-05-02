<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\JWTService;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{
    private $jwtService;
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, JWTService $jwtService)
    {
        $this->entityManager = $entityManager;
        $this->jwtService = $jwtService;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    #[Route('/user', name: 'app_update_user', methods: ['POST'])]
    public function updateUser(Request $request): JsonResponse
    {

        $tokenData = $this->jwtService->checkToken($request);
        if (is_bool($tokenData)) {
            return new JsonResponse($this->jwtService->sendJsonErrorToken($tokenData));
        }


        if (!$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }
        try {
            // Récupération de l'utilisateur courant
            $currentUser = $this->getUser()->getUserIdentifier();
            $user = $this->userRepository->findOneBy(['email' => $currentUser]);

            if (!$user) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Utilisateur non trouvé.',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Récupération des données de la requête
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');
            $tel = $request->request->get('tel');
            $sexe = $request->request->get('sexe');

            // Validation des données
            if ($sexe !== null && !in_array($sexe, ['0', '1'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.',

                ], 400);
            }

            if (isset($tel)) {
                $phoneRegex = '/^0[1-9][0-9]{8}$|^01[0-9]{8}$/';
                if (!preg_match($phoneRegex, $tel)) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Le format du numéro de téléphone est invalide.',
                    ], 400);
                }
            }

            if (isset($firstname) && (strlen($firstname) < 1 || strlen($firstname) > 60)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Erreur de validation des données.',
                ], 422);
            }

            if (isset($lastname) && (strlen($lastname) < 1 || strlen($lastname) > 60)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Erreur de validation des données.',
                ], 422);;
            }

            // Vérification des clés de la requête
            $keys = array_keys($request->request->all());
            $allowedKeys = ['firstname', 'lastname', 'tel', 'sexe'];
            $diff = array_diff($keys, $allowedKeys);
            if (count($diff) > 0) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les données fournies sont invalides ou incomplètes.',
                ], 400);
            }

            $existingUser = $this->userRepository->findOneBy(['tel' => $tel]);
            if ($existingUser && $existingUser !== $user) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Conflit de données. Le numéro de téléphone est déjà utilisé par un autre utilisateur.',
                ], 409);
            }

            // Mise à jour des données de l'utilisateur
            if ($firstname !== null) {
                $user->setFirstName($firstname);
            }
            if ($lastname !== null) {
                $user->setLastName($lastname);
            }
            if ($tel !== null) {
                $user->setTel($tel);
            }
            if ($sexe !== null) {
                $user->setSexe($sexe);
            }

            // Enregistrement des changements dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Réponse de succès
            return new JsonResponse([
                'error' => false,
                'message' => 'Votre inscription a bien été prise en compte',
            ]);
        } catch (\Exception $e) {
            // Gestion des erreurs
            return new JsonResponse([
                'error' => 'Error: ' . $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/account-deactivation', name: 'app_delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request): JsonResponse
    {
        $userData = $this->jwtService->checkToken($request);

        if (is_bool($userData)) {
            return $this->json($this->jwtService->sendJsonErrorToken($userData));
        }

        if (!$userData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentication requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }

        if ($userData->getIsActive() === 'INACTIVE') {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le compte est déjà désactivé.',
                'status' => 'Compte déjà désactivé'
            ], 409);
        }

        $user = $userData;

        $user->setIsActive('Inactive');
        $user->setUpdateAt(new DateTimeImmutable());

        // Deactivate associated artist profile if exists
        if ($user->getArtist()) {
            $artist = $user->getArtist();
            $artist->setIsActive('Inactive');
            $this->entityManager->persist($artist);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Votre compte a été avec succès.Nous sommes désolés de vous voir partir.',
            'status' => 'Succès'
        ], 201);
    }

    #[Route('/password-lost', name: 'app_reset_password', methods: ['POST'])]

    public function resetPassword(Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse
    {

        $requestData = $request->request->all();

        if ($request->headers->get('content-type') === 'application/json') {
            $requestData = json_decode($request->getContent(), true);
        }

        $email = $requestData['email'] ?? null;
        if (empty($email)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Email manquant. Veuillez fournir votre email pour la récupération du mot de passe.',
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'email est invalide. Veuillez entrer un email valide.',
            ], 400); // 400 Bad Request
        }


        // Rate limiter
        $cache = new FilesystemAdapter();
        $cacheKey = 'reset_password_' . urlencode($email);
        $cacheItem = $cache->getItem($cacheKey);
        $requestCount = $cacheItem->get() ?? 0;
        $timeToExpire = 300;
        $timeToExpireInMinutes = $timeToExpire / 60;

        if ($requestCount >= 3) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Trop de demandes de réinitialisation de mot de passe ( 3 max ). Veuillez attendre avant de réessayer ( Dans ' . $timeToExpireInMinutes . ' min).',
            ], 429);
        }

        $cacheItem->set($requestCount + 1);
        $cacheItem->expiresAfter($timeToExpire);
        $cache->save($cacheItem);

        $user = $this->userRepository->findOneBy(['email' => $email]);



        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Aucun compte n\'est associé à cet email. Veuillez vérifier et réessayer.',
            ], 404);
        }

        $token = $JWTManager->create($user);

        return new JsonResponse([
            'success' => true,
            'token' => $token,
            'message' => 'Un email de réinitialisation de mot de passe a été envoyé à votre adresse email. Veuillez suivre les instructions contenues dans l\'email pour réinitialiser votre mot de passe.',

        ]);
    }
}
