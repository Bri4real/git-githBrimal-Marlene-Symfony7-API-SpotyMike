<?php
namespace App\Controller;

use App\Entity\Song;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SongController extends AbstractController
{
    #[Route('/songs', name: 'create_song', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $requestData = json_decode($request->getContent(), true);

        $song = new Song();
        $song->setIdSong($requestData['idSong']);
        $song->setTitle($requestData['title']);
        $song->setUrl($requestData['url']);
        $song->setCover($requestData['cover']);
        $song->setVisibility($requestData['visibility']);
        $song->setCreateAt(new \DateTimeImmutable());

        $entityManager->persist($song);
        $entityManager->flush();

        return $this->json(['message' => 'Song created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/songs/{id}', name: 'get_song', methods: ['GET'])]
    public function get(int $id): Response
    {
        $song = $this->getDoctrine()->getRepository(Song::class)->find($id);

        if (!$song) {
            return $this->json(['error' => 'Song not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($song);
    }

    #[Route('/songs', name: 'get_songs', methods: ['GET'])]
    public function getAll(): Response
    {
        $songs = $this->getDoctrine()->getRepository(Song::class)->findAll();

        return $this->json($songs);
    }

    #[Route('/songs/{id}', name: 'update_song', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $song = $entityManager->getRepository(Song::class)->find($id);

        if (!$song) {
            return $this->json(['error' => 'Song not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $song->setIdSong($requestData['idSong']);
        $song->setTitle($requestData['title']);
        $song->setUrl($requestData['url']);
        $song->setCover($requestData['cover']);
        $song->setVisibility($requestData['visibility']);
       
        $entityManager->flush();

        return $this->json(['message' => 'Song updated successfully']);
    }

    #[Route('/songs/{id}', name: 'delete_song', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $song = $entityManager->getRepository(Song::class)->find($id);

        if (!$song) {
            return $this->json(['error' => 'Song not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($song);
        $entityManager->flush();

        return $this->json(['message' => 'Song deleted successfully']);
    }
}
