<?php
namespace App\Controller;

use App\Entity\Song;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SongController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/songs', name: 'song_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $songs = $this->getDoctrine()->getRepository(Song::class)->findAll();

        $data = [];
        foreach ($songs as $song) {
            $data[] = [
                'id' => $song->getId(),
                'title' => $song->getTitle(),
                'url' => $song->getUrl(),
                'cover' => $song->getCover(),
                // Ajoutez d'autres champs de chanson si nécessaire
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/songs/{id}', name: 'song_show', methods: ['GET'])]
    public function show(Song $song): JsonResponse
    {
        $data = [
            'id' => $song->getId(),
            'title' => $song->getTitle(),
            'url' => $song->getUrl(),
            'cover' => $song->getCover(),
            // Ajoutez d'autres champs de chanson si nécessaire
        ];

        return new JsonResponse($data);
    }

    #[Route('/songs', name: 'song_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $song = new Song();
        $song->setTitle($requestData['title']);
        $song->setUrl($requestData['url']);
        $song->setCover($requestData['cover']);
        // Ajoutez d'autres champs de chanson si nécessaire

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($song);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Song created successfully']);
    }

    #[Route('/songs/{id}', name: 'song_edit', methods: ['PUT'])]
    public function edit(Request $request, Song $song): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $song->setTitle($requestData['title']);
        $song->setUrl($requestData['url']);
        $song->setCover($requestData['cover']);
        // Mettez à jour d'autres champs de chanson si nécessaire

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['message' => 'Song updated successfully']);
    }

    #[Route('/songs/{id}', name: 'song_delete', methods: ['DELETE'])]
    public function delete(Song $song): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($song);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Song deleted successfully']);
    }
}
