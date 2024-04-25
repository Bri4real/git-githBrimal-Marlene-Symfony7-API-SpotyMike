<?php

namespace App\Controller;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/albums', name: 'create_album', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);

      
       

        $album = new Album();
        $nom = $request->request->get('nom');
        $cover = $request->request->get('cover');
        $categ = $request->request->get('categ');
        $year = $request->request->get('year');

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $this->json(['message' => 'Album created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/album/{id}', name: 'get_album', methods: ['GET'])]
    public function get(int $id): Response
    {
        $album = $this->entityManager->getRepository(Album::class)->find($id);

        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($album);
    }

    #[Route('/albums', name: 'get_albums', methods: ['GET'])]
    public function getAll(): Response
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        return $this->json($albums);
    }

    #[Route('/albums/{id}', name: 'update_album', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $album = $this->entityManager->getRepository(Album::class)->find($id);

        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $album = new Album();
        $album->setNom($requestData['nom']);
        $album->setCover($requestData['cover']);
        $album->setCateg($requestData['categ']);
        $album->setYear($requestData['year']);


        

        // Mettre à jour d'autres attributs si nécessaire

        $this->entityManager->flush();

        return $this->json(['message' => 'Album updated successfully']);
    }

    #[Route('/albums/{id}', name: 'delete_album', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $album = $this->entityManager->getRepository(Album::class)->find($id);

        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($album);
        $this->entityManager->flush();

        return $this->json(['message' => 'Album deleted successfully']);
    }
}