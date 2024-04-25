<?php

namespace App\Controller;

use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtistController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/artists', name: 'create_artist', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);

    

        $artist = new Artist();
        $fullname = $request->request->get('fullname');
        $label = $request->request->get('label');
        $description = $request->request->get('description');

        $this->entityManager->persist($artist);
        $this->entityManager->flush();

        return $this->json(['message' => 'Artist created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/artist/{id}', name: 'get_artist', methods: ['GET'])]
    public function get(int $id): Response
    {
        $artist = $this->entityManager->getRepository(Artist::class)->find($id);

        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($artist);
    }

    #[Route('/artists', name: 'get_artists', methods: ['GET'])]
    public function getAll(): Response
    {
        $artists = $this->entityManager->getRepository(Artist::class)->findAll();

        return $this->json($artists);
    }

    #[Route('/artists/{id}', name: 'update_artist', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $artist = $this->entityManager->getRepository(Artist::class)->find($id);

        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $artist = new Artist();
        $artist->setFullname($requestData['fullname']);
        $artist->setLabel($requestData['label']);
        $artist->setDescription($requestData['description']);
        

        // Mettre à jour d'autres attributs si nécessaire

        $this->entityManager->flush();

        return $this->json(['message' => 'Artist updated successfully']);
    }

    #[Route('/artists/{id}', name: 'delete_artist', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $artist = $this->entityManager->getRepository(Artist::class)->find($id);

        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($artist);
        $this->entityManager->flush();

        return $this->json(['message' => 'Artist deleted successfully']);
    }
}