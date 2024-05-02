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
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ArtistController extends AbstractController
{
    private $repository;
    private $entityManager;
    private $jwtService;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService, ValidatorInterface $validator)
    {
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->repository = $entityManager->getRepository(Artist::class);
    }



    #[Route('/artist/{fullname}', name: "app_get_artist_by_fullname", methods: ["GET"])]

    public function getArtistByFullname(Request $request, string $fullname): JsonResponse
    {
        // Valider le token
        $user = $this->getUser();
        if (!$user) {
            throw new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }

        // Valider le nom d'artiste
        $errors = $this->validator->validate($fullname, [
            new Assert\NotBlank(['message' => 'Le nom d\'artiste est obligatoire pour cette requête.']),
            new Assert\Regex([
                'pattern' => '/^[a-zA-Z0-9\s\p{P}]{1,30}$/',
                'message' => 'Le format du nom d\'artiste fourni est invalide.',
            ]),
        ]);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json([
                'error' => true,
                'message' => $errorMessages,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Rechercher l'artiste
        $artist = $this->entityManager->getRepository(Artist::class)->findOneBy(['fullname' => $fullname]);
        if (!$artist) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Aucun artiste trouvé correspondant au nom fourni.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }


        $currentArtist = $artist->getArtistInfo();

        return new JsonResponse([
            'error' => false,
            'artist' => $currentArtist,
        ]);
    }

    // I have to work on it cause Marlene ain't doing it .
    #[Route('/artist', name: 'app_create_update_artist', methods: ['POST'])]
    public function create_update_artist(Request $request): JsonResponse
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
                'status' => 'Paramètres invalides',
            ], 400);
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
        ], 200);
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
            'id_artist' => $artist->getUserIdUser(),
        ], 201);
    }

    #[Route('/artist', name: 'app_get_artists', methods: ['GET'])]
    public function getAllArtists(Request $request): JsonResponse
    {

        $tokenData = $this->jwtService->checkToken($request);

        if (is_bool($tokenData) || !$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Success',
            ], 401);
        }




        $artists = $this->entityManager->getRepository(Artist::class)->findAll();


        $limit = max(1, min(100, $request->query->getInt('limit', 5)));
        $page = max(1, $request->query->getInt('page', 1));

        if (!is_numeric($page) || $page <= 0) {
            return $this->json([
                'error' => true,
                'message' => 'Le paramètre de pagination est invalide. Veuillez fournir un numéro de page valide.',
                'status' => 'Paramètre de pagination invalide'
            ], 400);
        }

        $offset = ($page - 1) * $limit;
        $paginatedArtists = array_slice($artists, $offset, $limit);


        if (empty($paginatedArtists)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Aucun artiste trouvé pour la page demandée.',
                'status' => 'Aucun artiste trouvé',
            ], 404);
        }

        $AllArtists = array_map(fn ($artist) => $artist->getAllArtistsInfo(), $paginatedArtists);
        $totalArtists = count($artists);

        return new JsonResponse([
            'error' => false,
            'artists' => $AllArtists,
            'message' => 'Informations des artistes récupérées avec succès.',
            'status' => 'Success',
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($totalArtists / $limit),
                'totalArtists' => $totalArtists
            ]
        ], 200);
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
                'status'  => 'Artiste déjà désactivé'
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
