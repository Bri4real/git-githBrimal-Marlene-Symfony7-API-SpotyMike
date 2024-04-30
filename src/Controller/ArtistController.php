<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\JWTService;
use DateTime;
use DateInterval;
use App\Entity\Label;
use App\Entity\LabelHasArtist;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\Length;

class ArtistController extends AbstractController
{
    private $repository;
    private $entityManager;
    private $jwtService;

    public function __construct(EntityManagerInterface $entityManager, JWTService $jwtService)
    {
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
    // I have to work on it cause Marlene ain't doing it .
    #[Route('/artist', name: 'create_artist', methods: 'POST')]
    public function newArtist(Request $request)
    {
        $tokenData = $this->jwtService->checkToken($request);
        if (is_bool($tokenData)) {
            return new JsonResponse($this->jwtService->sendJsonErrorToken($tokenData));
        }

        if (!$tokenData) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
                'status' => 'Non authentifié'
            ], 401);
        }

        $user = $tokenData;
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }
    }




    #[Route('/albums', name: 'artist_update', methods: 'PUT')]
    public function update(Request $request, Artist $artist): JsonResponse
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Met à jour les propriétés de l'artiste avec les nouvelles données
        $artist->setFullname($data['fullname'] ?? $artist->getFullname());
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
