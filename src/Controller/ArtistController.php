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

    #[Route('/artist', name: 'artist_index', methods: 'GET')]
    public function index(): JsonResponse
    {
       
        $artists = $$this->repository->findAll();

      
        return $this->json($artists);
    }

    #[Route('/artist', name: 'artist_index', methods: 'GET')]
    public function show(Artist $artist): JsonResponse
    {
      
        return $this->json($artist);
    }

    #[Route('/artist', name: 'artist_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
       
        $data = json_decode($request->getContent(), true);

        
        $artist = new Artist();
        $artist->setFullname($data['fullname']);
        $artist->setLabel($data['label']);
        $artist->setDescription($data['description']);

        $this->entityManager->persist($artist);
        $$this->entityManager->flush();

        
        return $this->json($artist);
    }

    #[Route('/artist', name: 'artist_update', methods: 'PUT')]
    public function update(Request $request, Artist $artist): JsonResponse
    {
       
        $data = json_decode($request->getContent(), true);

        
        $artist->setFullname($data['fullname'] ?? $artist->getFullname());
        $artist->setLabel($data['label'] ?? $artist->getLabel());
        $artist->setDescription($data['description'] ?? $artist->getDescription());

       
        $this->entityManager->persist($artist);
        $$this->entityManager->flush();

        
        return $this->json($artist);
    }

    #[Route('/artist', name: 'artist_delete', methods: 'DELETE')]
    public function delete(Artist $artist): Response
    {
       

        $this->entityManager->remove($artist);
           $this->entityManager->flush();

        
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

