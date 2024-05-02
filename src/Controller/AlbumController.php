<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use App\Services\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\ArtistHasLabel;
use App\Entity\Label;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints\Json;

class AlbumController extends AbstractController
{
    private $Albumrepository;
    private $entityManager;
    private $jwtService;
    private $validator;
    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->Albumrepository = $entityManager->getRepository(Album::class);
        $validator = Validation::createValidator();
    }

    #[Route('/albums', name: 'album_index', methods: 'GET')]
    public function index(): JsonResponse
    {
        // Récupère tous les albums depuis la base de données
        $albums = $this->Albumrepository->findAll();

        // Retourne les albums sous forme de JSON
        return $this->json($albums);
    }



    /**
     * @Route("/albums/{id}", name="album_show", methods={"GET"})
     */
    public function show(Album $album): JsonResponse
    {
        // Retourne les détails d'un album spécifique sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums", name="album_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Crée une nouvelle instance de l'entité Album avec les données fournies
        $album = new Album();
        $album->setName($data['nom']);
        $album->setCover($data['cover']);
        $album->setCateg($data['categ']);
        $album->setYear($data['year']);

        // Obtient le gestionnaire d'entités et persiste l'album dans la base de données
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        // Retourne les données de l'album créé sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums/{id}", name="album_update", methods={"PUT"})
     */
    public function update(Request $request, Album $album): JsonResponse
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Met à jour les propriétés de l'album avec les nouvelles données
        $album->setName($data['nom'] ?? $album->getName());
        $album->setCover($data['cover'] ?? $album->getCover());
        $album->setCateg($data['categ'] ?? $album->getCateg());
        $album->setYear($data['year'] ?? $album->getYear());

        // Obtient le gestionnaire d'entités et met à jour l'album dans la base de données
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        // Retourne les données de l'album mis à jour sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums/{id}", name="album_delete", methods={"DELETE"})
     */
    public function delete(Album $album): Response
    {
        // Obtient le gestionnaire d'entités et supprime l'album de la base de données

        $this->entityManager->remove($album);
        $this->entityManager->flush();
        // Retourne une réponse vide avec un code de statut HTTP indiquant la suppression réussie
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/albums', name: 'app_get_all_albums', methods: ['GET'])]
    public function AllAlbums(Request $request): JsonResponse
    {

        $tokenData = $this->jwtService->checkToken($request);
        if (is_bool($tokenData) || !$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Success',
            ], 401);
        }

        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        $AllAlbums = [];
        foreach ($albums as $album) {
            $AllAlbums[] = $album->getAllAlbums();
        }

        $total = count($AllAlbums);
        $limit = $request->query->get('limit', 5);
        $page =  $request->query->get('page');

        if (!is_numeric($limit) || $limit <= 0) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le paramètre de pagination est invalide. Veuillez fournir un numéro de page valide',
                'status' => 'Paramètre de pagination invalide'
            ], 400);
        }

        if (empty($page) || !is_numeric($page) || $page <= 0) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le paramètre de pagination est invalide. Veuillez fournir un numéro de page valide',
                'status' => 'Paramètre de pagination invalide'
            ], 400);
        }



        $offset = ($page - 1) * $limit;
        $pagination = array_slice($AllAlbums, $offset, $limit);

        if (empty($pagination)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Aucun album trouvé pour la page demandée.',
                'status' => 'Album non trouvé'
            ], 404);
        }

        return new JsonResponse([
            'error' => false,
            'albums' => $pagination,
            'pagination' => [
                'currentPage' => (int)$page,
                'totalPages' => ceil($total / $limit),
                'totalAlbums' => $total,
            ],
        ]);
    }

    #[Route('/album', name: 'app_create_album', methods: ['POST'])]
    public function createAlbum(Request $request): JsonResponse
    {
        $tokenData = $this->jwtService->checkToken($request);
        if (is_bool($tokenData) || !$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user = $tokenData;

        $artist = $this->entityManager->getRepository(Artist::class)->findOneBy(['User_idUser' => $user->getIdUser()]);
        if (!$artist) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Vous n\'avez pas l\'autorisation pour accéder à cet album.',
                'status' => 'Accès refusé / Non autorisé'
            ], Response::HTTP_FORBIDDEN);
        }

        $requestData = $request->request->all();
        $validator = Validation::createValidator();

        $violations = $validator->validate($requestData, new Assert\Collection([
            'visibility' => new Assert\Choice(['0', '1']),
            'cover' => new Assert\NotBlank(),
            'title' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 90]),
            ],
            'categorie' => new Assert\Json([
                'message' => 'Le format de la chaîne JSON est invalide.',
            ]),
        ]));

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return new JsonResponse([
                'error' => true,
                'message' => 'Erreurs de validation des données',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $categ = json_decode($requestData['categorie'], true);
        $missingCateg = array_diff($categ, ["rap", "r'n'b", "gospel", "soul", "country", "hip hop", "jazz", "le Mike"]);
        if (!empty($missingCateg)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Les catégorie ciblée sont invalide.',
                'status' => 'Categorie Invalide'
            ], Response::HTTP_BAD_REQUEST);
        }

        $existingAlbumName = $this->entityManager->getRepository(Album::class)->findOneBy(['title' => $requestData['title']]);
        if ($existingAlbumName) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Ce titre est déjà pris. Veuillez en choisir un autre.',
                'status' => 'titre d\'album déjà utilisé'
            ], Response::HTTP_CONFLICT);
        }

        $cover = $requestData['cover'];
        [$coverHeader, $coverData] = explode(',', $cover);

        if (strpos($coverHeader, 'data:image/') !== 0) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le serveur ne peut pas décoder le contenu base64 en fichier binaire.',
                'status' => 'Erreur de décodage'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $convertCover = base64_decode($coverData);
        $imageSizeBytes = strlen($convertCover);
        $imageSizeMB = $imageSizeBytes / (1024 * 1024);
        if (!($imageSizeMB >= 1 && $imageSizeMB <= 7)) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le fichier envoyé est trop ou pas assez volumineux. Vous devez respecter la taille entre 1MB et 7MB.",
                'status' => 'Format de fichier non pris en charge'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $prefix = 'spmikeAlbumCover::';
        $format = str_replace('data:image/', '', explode(';', $coverHeader)[0]);
        $uuid = Uuid::uuid4()->toString();
        $img = $prefix . $uuid . '.' . $format;
        $Path = $this->getParameter('Img_Folder') . '/' . $img;
        file_put_contents($Path, $convertCover);
        $category = json_decode($request->request->get('categorie'), true);

        $album = new Album();
        $album->setIdAlbum();
        $album->setName($request->request->get('title'));
        $album->setCateg($category);
        $album->setVisibility($request->request->get('visibility'));
        $album->setYear(new \DateTime(date("Y")));
        $album->setArtistUserIdUser($user->getArtist());
        $album->setCover($img);

        try {
            $this->entityManager->persist($album);
            $this->entityManager->flush();

            return new JsonResponse([
                'error' => false,
                'message' => 'Album créé avec succès',
                'id' => $album->getIdAlbum()
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Une erreur s\'est produite lors de l\'interaction avec la base de données.',
                'status' => 'Erreur de base de données'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
