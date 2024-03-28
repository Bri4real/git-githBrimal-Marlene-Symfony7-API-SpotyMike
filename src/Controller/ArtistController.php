<?php




namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Artiste;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtistController extends AbstractController
{
    private $repository;
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Artist::class);
    }

    #[Route('/albums', name: 'artist_index', methods: 'GET')]
    public function index(): JsonResponse
    {
        // Récupère tous les artistes depuis la base de données
        $artists = $$this->repository->findAll();

        // Retourne les artistes sous forme de JSON
        return $this->json($artists);
    }

    #[Route('/albums', name: 'artist_index', methods: 'GET')]
    public function show(Artist $artist): JsonResponse
    {
        // Retourne les détails d'un artiste spécifique sous forme de JSON
        return $this->json($artist);
    }

    #[Route('/albums', name: 'artist_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Crée une nouvelle instance de l'entité Artiste avec les données fournies
        $artist = new Artist();
        $artist->setFullname($data['fullname']);
        $artist->setLabel($data['label']);
        $artist->setDescription($data['description']);

        // Obtient le gestionnaire d'entités et persiste l'artiste dans la base de données
        $this->entityManager->persist($artist);
        $$this->entityManager->flush();

        // Retourne les données de l'artiste créé sous forme de JSON
        return $this->json($artist);
    }

    #[Route('/albums', name: 'artist_update', methods: 'PUT')]
    public function update(Request $request, Artist $artist): JsonResponse
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Met à jour les propriétés de l'artiste avec les nouvelles données
        $artist->setFullname($data['fullname'] ?? $artist->getFullname());
        $artist->setLabel($data['label'] ?? $artist->getLabel());
        $artist->setDescription($data['description'] ?? $artist->getDescription());

        // Obtient le gestionnaire d'entités et met à jour l'artiste dans la base de données
        $this->entityManager->persist($artist);
        $$this->entityManager->flush();

        // Retourne les données de l'artiste mis à jour sous forme de JSON
        return $this->json($artist);
    }

    #[Route('/albums', name: 'artist_delete', methods: 'DELETE')]
    public function delete(Artist $artist): Response
    {
        // Obtient le gestionnaire d'entités et supprime l'artiste de la base de données

        $this->entityManager->remove($artist);
           $this->entityManager->flush();

        // Retourne une réponse vide avec un code de statut HTTP indiquant la suppression réussie
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

