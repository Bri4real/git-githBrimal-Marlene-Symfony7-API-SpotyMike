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

        return $this->json($albums);
    }

   

    #[Route('/albums', name: 'album_show', methods: 'GET')]
    public function show(Album $album): JsonResponse
    {
   
        return $this->json($album);
    }

    
     #[Route('/albums', name: 'artist_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);

 
        $album = new Album();
        $album->setNom($data['nom']);
        $album->setCover($data['cover']);
        $album->setCateg($data['categ']);
        $album->setYear($data['year']);

      
         $this->entityManager->persist($album);
         $this->entityManager->flush();

       
        return $this->json($album);
    }

   
    #[Route('/albums', name: 'album_update', methods: 'PUT')]
    public function update(Request $request, Album $album): JsonResponse
    {
      
        $data = json_decode($request->getContent(), true);

       
        $album->setNom($data['nom'] ?? $album->getNom());
        $album->setCover($data['cover'] ?? $album->getCover());
        $album->setCateg($data['categ'] ?? $album->getCateg());
        $album->setYear($data['year'] ?? $album->getYear());

        
        $this->entityManager->persist($album);
        $this->entityManager->flush();


        return $this->json($album);
    }

  
    #[Route('/albums', name: 'album_delete', methods: 'DELETE')]
    public function delete(Album $album): Response 
    {
       
        $this->entityManager->remove($album);
        $this->entityManager->flush();
        
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
