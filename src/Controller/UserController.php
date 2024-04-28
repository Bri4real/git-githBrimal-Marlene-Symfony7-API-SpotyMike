<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\JWTService;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private $jwtService;
    private $entityManager;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService)
    {
        $this->entityManager = $entityManager;
        $this->jwtService = $jwtService;
        $this->repo = $entityManager->getRepository(User::class);
    }
    #[Route('/user', name: 'update_user', methods: 'POST')]
    public function updateUser(Request $request): JsonResponse
    {
        try {
            // Récupération de l'utilisateur actuellement connecté
            $userData = $this->getUser()->getUserIdentifier();
            if ($userData) {
                // Recherche de l'utilisateur dans la base de données
                $user = $this->repo->findOneBy(['email' => $userData]);
            } else {
                // Retourne une réponse si l'utilisateur n'est pas authentifié
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            // Récupération des données de la requête
            $firstName = $request->request->get('firstname');
            $lastName = $request->request->get('lastname');
            $tel = $request->request->get('tel');
            $sexe = $request->request->get('sexe');

            // Pattern pour vérifier le format du numéro de téléphone
            $phoneRegex = '/^0[1-9][0-9]{8}$|^01[0-9]{8}$/';

            // Vérification de la validité du champ 'sexe'
            if ($sexe !== null && !in_array($sexe, ['0', '1'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.',
                    'status' => 'Valeur de sexe invalide '
                ], 400);
            }

            // Vérification du format du numéro de téléphone
            if (isset($tel) && !preg_match($phoneRegex, $tel)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Le format du numéro de téléphone est invalide.',
                    'status' => 'Format de téléphone invalide '
                ], 400);
            }

            // Vérification de la longueur des champs 'firstname' et 'lastname'
            if (
                isset($firstName) && (strlen($firstName) < 1 || strlen($firstName) > 60) ||
                isset($lastName) && (strlen($lastName) < 1 || strlen($lastName) > 60)
            ) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les données fournies sont invalides ou incomplètes.',
                    'status' => 'Données fournies non valides '
                ], 400);
            }

            // Vérification des clés de la requête
            $allowedKeys = ['firstname', 'lastname', 'tel', 'sexe'];
            $diff = array_diff(array_keys($request->request->all()), $allowedKeys);
            if (count($diff) > 0) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les données fournies sont invalides ou incomplètes.',
                    'status' => 'Données fournies non valides '
                ], 400);
            }

            // Vérification de conflit de données pour le numéro de téléphone
            $existingUser = $this->repo->findOneBy(['tel' => $tel]);
            if ($existingUser && $existingUser !== $user) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Conflit de données. Le numéro de téléphone est déjà utilisé par un autre utilisateur.',
                    'status' => 'Conflit dans les données'
                ], 409);
            }

            // Mise à jour des données de l'utilisateur
            if ($firstName !== null) {
                $user->setFirstName($firstName);
            }
            if ($lastName !== null) {
                $user->setLastName($lastName);
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
                'status' => 'Succès'
            ], 201);
        } catch (\Exception $e) {
            // Gestion des erreurs
            return new JsonResponse([
                'error' => 'Error: ' . $e->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);
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
            return $this->json([
                'error' => true,
                'message' => 'Authentication requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }

        if ($userData->getActive() === 'Inactive') {
            return $this->json([
                'error' => true,
                'message' => 'Le compte est déjà désactivé.',
                'status' => 'Compte déjà désactivé'
            ], 409);
        }

        $user = $userData;

        $user->setActive('Inactive');
        $user->setUpdateAt(new DateTimeImmutable());

        // Deactivate associated artist profile if exists
        if ($user->getArtist()) {
            $artist = $user->getArtist();
            $artist->setActive('Inactive');
            $this->entityManager->persist($artist);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Votre compte a été avec succès.Nous sommes désolés de vous voir partir.',
            'status' => 'Succès'
        ], 200);
    }
}
