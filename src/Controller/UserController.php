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
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    #[Route('/user', name: 'app_user_update', methods: ['POST'])]
    public function updateUser(Request $request): JsonResponse
    {
        $userPayload = $this->userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $user = $this->userRepository->find($userPayload->getIdUser());

        if (!$userPayload) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié',
                'code' => JsonResponse::HTTP_UNAUTHORIZED
            ]);
        }

        $user = $this->userRepository->findOneBy($userPayload['email']);
        $requestData = $request->request->all();

        // Vérifier si les clés fournies sont autorisées
        $allowedKeys = ['firstname', 'lastname', 'tel', 'sexe'];
        $missingKeys = array_diff(array_keys($requestData), $allowedKeys);
        if (!empty($missingKeys)) {
            return $this->json([
                'error' => true,
                'message' => 'Les données fournies sont invalides ou incomplètes.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Valider les données
        $tel = $requestData['tel'] ?? null;
        if ($tel !== null && !preg_match('/^0[6-7][0-9]{8}$/', $tel)) {
            return $this->json([
                'error' => true,
                'message' => 'Le format du numéro de téléphone est invalide.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $sexe = $requestData['sexe'] ?? null;
        if ($sexe !== null && !in_array($sexe, [0, 1])) {
            return $this->json([
                'error' => true,
                'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier si le numéro de téléphone est déjà utilisé
        if ($tel !== null) {
            $existingUser = $this->userRepository->findOneBy(['tel' => $tel]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return $this->json([
                    'error' => true,
                    'message' => 'Conflit de données. Le numéro de téléphone est déjà utilisé par un autre utilisateur.',
                ], JsonResponse::HTTP_CONFLICT);
            }
        }

        // Mettre à jour les données de l'utilisateur
        foreach ($requestData as $key => $value) {
            switch ($key) {
                case 'firstname':
                    $user->setFirstname($value);
                    break;
                case 'lastname':
                    $user->setLastname($value);
                    break;
                case 'tel':
                    $user->setTel($value);
                    break;
                case 'sexe':
                    $user->setSexe($value);
                    break;
                default:
                    break;
            }
        }

        $user->setUpdateAt(new DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Votre mise à jour a bien été prise en compte.',
        ]);
    }
}
