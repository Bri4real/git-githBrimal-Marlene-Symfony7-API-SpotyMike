<?php
namespace App\Controller;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    private $repository;
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Album::class);
    }
    
    #[Route('/albums', name: 'album_index', methods: 'GET')]
    public function index(): JsonResponse
    {
        // Récupère tous les albums depuis la base de données
        $albums =$this->repository->findAll();

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
        $album->setNom($data['nom']);
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
        $album->setNom($data['nom'] ?? $album->getNom());
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
}
