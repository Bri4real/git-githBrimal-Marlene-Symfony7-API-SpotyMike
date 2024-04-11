<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
    
        $indexFilePath = __DIR__ . '/../../public/index.html';

        
        if (file_exists($indexFilePath)) {
            
            return new Response(file_get_contents($indexFilePath), Response::HTTP_OK);
        } else {
           
            return new Response('Envoie de la page 404', Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/donnees', name: 'donnees')]
    public function donnees(): Response
    {
        
        return new Response('Aucune donnée.', Response::HTTP_I_AM_A_TEAPOT);
    }
}
