<?php

// namespace App\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Routing\Attribute\Route;

//class ArtistController extends AbstractController
//{
    //#[Route('/artist', name: 'app_artist')]
    //public function index(): JsonResponse
    //{
       // return $this->json([
         //   'message' => 'Welcome to your new controller!',
           // 'path' => 'src/Controller/ArtistController.php',
        //]);
    //}
//}






namespace App\Controller;

use App\Entity\Artiste;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtisteController extends AbstractController
{
    /**
     * @Route("/artiste", name="artiste_index", methods={"GET"})
     */
    public function index(): Response
    {
        // Récupère tous les artistes depuis la base de données
        $artistes = $this->getDoctrine()->getRepository(Artiste::class)->findAll();

        // Retourne les artistes sous forme de JSON
        return $this->json($artistes);
    }

    /**
     * @Route("/artiste/{id}", name="artiste_show", methods={"GET"})
     */
    public function show(Artiste $artiste): Response
    {
        // Retourne les détails d'un artiste spécifique sous forme de JSON
        return $this->json($artiste);
    }

    /**
     * @Route("/artiste", name="artiste_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Crée une nouvelle instance de l'entité Artiste avec les données fournies
        $artiste = new Artiste();
        $artiste->setFullname($data['fullname']);
        $artiste->setLabel($data['label']);
        $artiste->setDescription($data['description']);
        $artiste->setArtistcol($data['artistcol']);

        // Obtient le gestionnaire d'entités et persiste l'artiste dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($artiste);
        $entityManager->flush();

        // Retourne les données de l'artiste créé sous forme de JSON
        return $this->json($artiste);
    }

    /**
     * @Route("/artiste/{id}", name="artiste_update", methods={"PUT"})
     */
    public function update(Request $request, Artiste $artiste): Response
    {
        // Récupère les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Met à jour les propriétés de l'artiste avec les nouvelles données
        $artiste->setFullname($data['fullname'] ?? $artiste->getFullname());
        $artiste->setLabel($data['label'] ?? $artiste->getLabel());
        $artiste->setDescription($data['description'] ?? $artiste->getDescription());
        $artiste->setArtistcol($data['artistcol'] ?? $artiste->getArtistcol());

        // Obtient le gestionnaire d'entités et met à jour l'artiste dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Retourne les données de l'artiste mis à jour sous forme de JSON
        return $this->json($artiste);
    }

    /**
     * @Route("/artiste/{id}", name="artiste_delete", methods={"DELETE"})
     */
    public function delete(Artiste $artiste): Response
    {
        // Obtient le gestionnaire d'entités et supprime l'artiste de la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($artiste);
        $entityManager->flush();

        // Retourne une réponse vide avec un code de statut HTTP indiquant la suppression réussie
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

