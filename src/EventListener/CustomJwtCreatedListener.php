<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\UserRepository; // Assurez-vous que le chemin est correct pour UserRepository

class JWTCreatedListener
{
    private $requestStack;
    private $userRepository;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        // Récupérer la requête courante depuis RequestStack
        $request = $this->requestStack->getCurrentRequest();

        // Récupérer les données de payload actuelles de l'événement
        $payload = $event->getData();

        // Récupérer le nom d'utilisateur du payload
        $username = $payload['username'];

        // Rechercher l'utilisateur dans le dépôt UserRepository en utilisant le nom d'utilisateur
        $user = $this->userRepository->findOneByEmail($username);

        // Vérifier si l'utilisateur existe
        if ($user) {
            // Ajouter des données utilisateur supplémentaires au payload
            $payload['email'] = $user->getEmail();
            $payload['iduser'] = $user->getIdUser();
            // Ajoutez d'autres données personnalisées selon vos besoins
        }

        // Définir les données de payload mises à jour dans l'événement
        $event->setData($payload);
    }
}
