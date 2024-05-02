<?php

namespace App\Controller;

use App\Entity\Album;
use App\Services\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    private $Albumrepository;
    private $entityManager;
    private $jwtService;
    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->Albumrepository = $entityManager->getRepository(Album::class);
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
    public function getAllAlbums(Request $request): JsonResponse
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
}
