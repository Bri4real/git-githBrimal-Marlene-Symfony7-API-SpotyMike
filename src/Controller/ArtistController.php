<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\JWTService;
use DateTime;
use App\Entity\Label;
use App\Entity\LabelHasArtist;
use DateTimeImmutable;
use PhpParser\Node\Stmt\Return_;

class ArtistController extends AbstractController
{
    private $repository;
    private $entityManager;
    private $jwtService;

    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Artist::class);
    }


    // I have to work on it cause Marlene ain't doing it .
    #[Route('/artist', name: 'app_create_artist', methods: ['POST'])]
    public function createArtist(Request $request): JsonResponse
    {
        $tokenData = $this->checkUser($request);

        if ($tokenData instanceof JsonResponse) {
            return $tokenData;
        }

        $user = $tokenData;

        if (!$user) {
            return new JsonResponse(['message' => 'User non trouvé'], 404);
        }
        $age = $user->getDateBirth()->diff(new DateTime())->y;
        if ($age < 16) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Vous devez avoir au moins 16 ans pour être artiste.',
                'status' => 'User non éligible pour être artiste. '
            ], 400);
        }

        $requestData = $this->extractRequestData($request);

        if ($user->getArtist() !== null) {
            return $this->updateArtist($user, $requestData);
        } else {
            return $this->createNewArtist($user, $requestData);
        }
    }

    private function checkUser(Request $request)
    {
        $tokenData = $this->jwtService->checkToken($request);

        if (is_bool($tokenData)) {
            return $this->jwtService->sendJsonErrorToken($tokenData);
        }

        if (!$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }

        return $tokenData;
    }

    private function extractRequestData(Request $request)
    {
        $requestData = $request->request->all();

        if ($request->headers->get('content-type') === 'application/json') {
            $requestData = json_decode($request->getContent(), true);
        }

        return $requestData;
    }

    private function updateArtist($user, $requestData)
    {
        // Validation des données pour la mise à jour
        $invalidData = [];
        $invalidIdLabel = false;

        if (isset($requestData['fullname'])) {
            $fullname = $requestData['fullname'];
            if (!preg_match('/^[a-zA-Z0-9\s\p{P}]{1,30}$/', $fullname) || strlen($fullname) > 90) {
                $invalidData[] = 'fullname';
            }
            $duplicateArtiste = $this->repository->findOneBy(['fullname' => $requestData['fullname']]);
            if ($duplicateArtiste && $duplicateArtiste->getId() !== $user->getArtist()->getId()) { //gotta come back to check this 
                $invalidData[] = 'fullname';
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Ce nom d\'artiste est déjà pris. Veuillez choisir un autre.',
                    'status' => 'Nom d\'artiste déjà utilisé'
                ], 409);
            }
        }

        if (isset($requestData['label']) && strlen($requestData['label']) > 90) {
            $invalidIdLabel = true;
        }

        if (!empty($invalidData)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Les paramètres fournis sont invalides. Veuillez vérifier les données soumises.',
            ],);
        }

        if ($invalidIdLabel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'id du label est invalide.',
            ], 400);
        }

        $artist = $user->getArtist();

        if (isset($requestData['fullname'])) {
            $artist->setFullname($requestData['fullname']);
        }
        if (isset($requestData['label'])) {
            $labelId = $requestData['label'];
            $label = $this->entityManager->getRepository(Label::class)->findOneBy(['idLabel' => $labelId]);



            if (!$label) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Le format de l\'id du label est invalide.',
                ], 400);
            }

            $prevLabelRel = $this->entityManager->getRepository(LabelHasArtist::class)->findOneBy(['idArtist' => $artist, 'leftAt' => null]);
            if ($prevLabelRel) {
                $prevLabelRel->setLeftAt(new DateTime());
                $this->entityManager->persist($prevLabelRel);
                $this->entityManager->flush();
            }

            $artist->setUpdatedAt(new DateTimeImmutable());
            $labelHasArtist = new LabelHasArtist();
            $labelHasArtist->setIdArtist($artist);
            $labelHasArtist->setIdLabel($label);
            $labelHasArtist->setSignAt(new DateTime());
            $labelHasArtist->setLeftAt(null);
            $this->entityManager->persist($labelHasArtist);
            $this->entityManager->flush();
        }

        if (isset($requestData['description'])) {
            $artist->setDescription($requestData['description'] ?? null);
        }

        if (isset($requestData['avatar'])) {
            // Logic for updating avatar
        }

        $this->entityManager->persist($artist);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Les informations de l\'artiste ont été mises à jour avec succès.',
        ], JsonResponse::HTTP_CREATED);
    }

    private function createNewArtist($user, $requestData)
    {
        // Validation des données pour la création d'un nouvel artiste
        $requiredFields = ['fullname', 'label'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'l\'id du label et le fullname sont obligatoires.',
                'status' => 'Données obligatoires manquantes'
            ], 400);
        }

        $invalidData = [];
        $invalidIdLabel = false;

        if (isset($requestData['fullname'])) {
            $fullname = $requestData['fullname'];
            if (strlen($fullname) < 1 || strlen($fullname) > 30) {
                $invalidData[] = 'fullname';
            }
            $duplicateArtiste = $this->repository->findOneBy(['fullname' => $fullname]);
            if ($duplicateArtiste) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Ce nom d\'utilisateur est déjà pris. Veuillez choisir un autre.',
                ], 400);
            }
        }


        if (isset($requestData['label']) && strlen($requestData['label']) > 60) {
            $invalidIdLabel = true;
        }

        if (!empty($invalidData)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Les données fournies sont invalides ou incomplètes.',
            ], JsonResponse::HTTP_CONFLICT);
        }

        if ($invalidIdLabel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'id du label est invalide.',
                'status' => 'Format de l\id du label invalide'
            ], 400);
        }


        $labelId = $requestData['label'];
        $label = $this->entityManager->getRepository(Label::class)->findOneBy(['idLabel' => $labelId]);

        if (!$label) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Label Fourni Invalide ',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $artist = new Artist();
        $artist->setUserIdUser($user);
        $artist->setFullname($requestData['fullname']);
        $artist->setDescription($requestData['description'] ?? null);
        $artist->setIsActive('ACTIVE');
        $artist->setCreatedAt(new DateTimeImmutable());
        $artist->setUpdatedAt(new DateTimeImmutable());

        $labelHasArtist = new LabelHasArtist();
        $labelHasArtist->setIdArtist($artist);
        $labelHasArtist->setIdLabel($label);
        $labelHasArtist->setSignAt(new DateTime());
        $labelHasArtist->setLeftAt(null);

        if (isset($requestData['avatar'])) {
        }

        $this->entityManager->persist($labelHasArtist);
        $this->entityManager->persist($artist);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Votre compte artiste a été créé avec succès. Bienvenue dans notre communauté d\'artistes !',
            'id_artist' => strval($artist->getUserIdUser()),
        ], 201);
    }
    #[Route('/artist', name: 'app_delete_artist', methods: ['DELETE'])]
    public function delete(Request $request)
    {
        $tokenData = $this->jwtService->checkToken($request);
        if (is_bool($tokenData)) {
            return new JsonResponse($this->jwtService->sendJsonErrorToken($tokenData));
        }

        if (!$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentication requise. Vous devez être connecté pour effectuer cette action.',
            ], 401);
        }

        $user = $tokenData;

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utiliateur non trouvé',
            ], 404);
        }
        if ($user->getArtist() === null) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Compte artiste non trouvé. Veuillez vérifier les informations fournies et réessayez.',
                'status'  => 'Artiste non trouvé'
            ], 404);
        }

        if ($user->getArtist()->getIsActive() ===  'INACTIVE') {
            return new JsonResponse([
                'error' => true,
                'message' => 'Ce compte artiste est déjà désactivé.',
                'status'  => 'Artistedéjà désactivé'
            ], 410);
        }

        $artist = $user->getArtist();
        $artist->setIsActive('INACTIVE');

        $this->entityManager->persist($artist);
        $this->entityManager->flush();

        return new JsonResponse([
            'error' => false,
            'message' => 'Le compte a été désactivé avec succès',
        ], 200);
    }
}
