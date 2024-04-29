<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Cache\CacheItemPoolInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class LoginController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $cache;
    private $JWTManager;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, CacheItemPoolInterface $cache, JWTTokenManagerInterface $JWTManager)
    {
        $this->entityManager = $entityManager;
        $this->repo = $entityManager->getRepository(User::class);
        $this->passwordHasher = $passwordHasher;
        $this->cache = $cache;
        $this->JWTManager = $JWTManager;
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Vérification des données obligatoires
        if (!$email || !$password) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Email/password manquants.',
                'status' => 'Donnée manquante',
            ], 400);
        }

        $email = $request->request->get('email'); // Correction de la variable
        if (!$this->checkEmail($email)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'email est invalide.',
                'status' => 'Format d\'email invalide'
            ], 400);
        }

        // Vérification du format du mot de passe
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial et doit avoir au moins 8 caractères.',
                'status' => 'Le mot de passe ne respecte pas les critères',
            ], 400);
        }

        // Gestion des tentatives de connexion
        $this->handleLoginAttempts($email);

        // Récupération de l'utilisateur
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérification de l'état du compte utilisateur
        if ($user && $user->getIsActive() !== 'ACTIVE') {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le compte n\'est plus actif ou suspendu.',
                'status' => 'Compte non activé ou suspendu',
            ], 403);
        }

        // Vérification des identifiants de connexion
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Échec d\'authentification. Veuillez réessayer.',
                'status' => 'Échec de connexion',
            ], 401);
        }

        // Création du jeton JWT
        $token = $this->JWTManager->create($user);

        return new JsonResponse([
            'error' => false,
            'message' => 'L\'utilisateur a été authentifié avec succès',
            'user' => $user->loginSerializer(),
            'token' => $token,
        ]);
    }

    private function checkEmail(?string $email): bool
    {
        if ($email === null) {
            return false; // Changement pour retourner false ici
        }
        $regex = '/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/';
        return preg_match($regex, $email) === 1;
    }

    private function handleLoginAttempts(string $email): void
    {
        // Instanciation du cache
        $cache = new FilesystemAdapter();

        // Clé de cache pour le nombre de tentatives de connexion de l'utilisateur
        $cacheKey = 'login_' . urlencode($email);

        $cacheItem = $cache->getItem($cacheKey);
        $requestCount = $cacheItem->get() ?? 0;
        $maxLoginAttempts = 5;

        // Si le nombre de tentatives de connexion dépasse le maximum autorisé
        if ($requestCount >= $maxLoginAttempts) {
            $timeToExpire = 30;
            // Conversion en minutes pour une meilleure lisibilité
            $timeToExpireInMinutes = $timeToExpire / 60;

            // Réponse avec un message d'erreur indiquant le temps d'attente nécessaire
            $response = new JsonResponse([
                'error' => true,
                'message' => 'Trop de tentatives de connexion (maximum 5). Veuillez réessayer ultérieurement - ' . $timeToExpireInMinutes . ' minutes d\'attente.',
                'status' => 'Trop de tentatives (Rate Limiting)'
            ], 429);

            // Envoi de la réponse et arrêt de l'exécution du script
            $response->send();
            exit();
        }

        // Incrémentation du nombre de tentatives de connexion dans le cache
        $cacheItem->set($requestCount + 1);
        // Définition de l'expiration de l'entrée dans le cache (5 secondes dans cet exemple)
        $cacheItem->expiresAfter(250);
        $cache->save($cacheItem);
    }
}
