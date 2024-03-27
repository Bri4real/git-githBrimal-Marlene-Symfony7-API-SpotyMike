<?php

//namespace App\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Routing\Attribute\Route;

//class AlbumController extends AbstractController
//{
  //  #[Route('/album', name: 'app_album')]
    // public function index(): JsonResponse
    // {
       //  return $this->json([
           // 'message' => 'Welcome to your new controller!',
             //'path' => 'src/Controller/AlbumController.php',
        // ]);
  //  }
// }



namespace App\Controller;

use App\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    /**
     * @Route("/albums", name="album_index", methods={"GET"})
     */
    public function index(): Response
    {
        // Récupère tous les albums depuis la base de données
        $albums = $this->getDoctrine()->getRepository(Album::class)->findAll();

        // Retourne les albums sous forme de JSON
        return $this->json($albums);
    }

    /**
     * @Route("/albums/{id}", name="album_show", methods={"GET"})
     */
    public function show(Album $album): Response
    {
        // Retourne les détails d'un album spécifique sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums", name="album_create", methods={"POST"})
     */
    public function create(Request $request): Response
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
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($album);
        $entityManager->flush();

        // Retourne les données de l'album créé sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums/{id}", name="album_update", methods={"PUT"})
     */
    public function update(Request $request, Album $album): Response
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Met à jour les propriétés de l'album avec les nouvelles données
        $album->setNom($data['nom'] ?? $album->getNom());
        $album->setCover($data['cover'] ?? $album->getCover());
        $album->setCateg($data['categ'] ?? $album->getCateg());
        $album->setYear($data['year'] ?? $album->getYear());

        // Obtient le gestionnaire d'entités et met à jour l'album dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Retourne les données de l'album mis à jour sous forme de JSON
        return $this->json($album);
    }

    /**
     * @Route("/albums/{id}", name="album_delete", methods={"DELETE"})
     */
    public function delete(Album $album): Response
    {
        // Obtient le gestionnaire d'entités et supprime l'album de la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($album);
        $entityManager->flush();

        // Retourne une réponse vide avec un code de statut HTTP indiquant la suppression réussie
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
