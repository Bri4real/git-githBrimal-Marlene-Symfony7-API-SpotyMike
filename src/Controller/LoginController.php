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

class LoginController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $cache;
    private $JWTManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, CacheItemPoolInterface $cache, JWTTokenManagerInterface $JWTManager)
    {
        $this->entityManager = $entityManager;
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
            return new JsonResponse(['error' => true, 'message' => 'L\'e-mail et le mot de passe sont requis'], 400);
        }

        // Récupération de l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérification de l'utilisateur
        if (!$user) {
            return $this->handleLoginFailure($email);
        }

        // Vérification du mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->handleLoginFailure($email);
        }
        // Vérification du mot de passe haché
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            // Générer un hash pour le mot de passe fourni
            $providedPasswordHash = $this->passwordHasher->hashPassword($user, $password);

            // Comparer le hash fourni avec le hash stocké
            if (!$this->passwordHasher->isPasswordValid($user, $providedPasswordHash)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Mot de passe incorrect'
                ], 400);
            }
            // Sinon, retourner l'erreur de connexion par défaut
            return $this->handleLoginFailure($email);
        }


        // Génération du token JWT
        $token = $this->JWTManager->create($user);

        // Construction de la réponse avec les données utilisateur et le token JWT
        return new JsonResponse([
            'error' => false,
            'message' => 'L\'utilisateur a été authentifié avec succès',
            'user' => [
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'tel' => $user->getTel(),
                'sexe' => $user->getSexe(),
                'artist' => $user->getArtist(),
                'dateBirth' => $user->getDateBirth()->format('Y-m-d'),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
            'token' => $token,
        ]);
    }

    private function handleLoginFailure(string $email): JsonResponse
    {
        // Vérification du nombre de tentatives sur cet email
        $cacheKey = 'login_attempts_' . md5($email);
        $cacheItem = $this->cache->getItem($cacheKey);
        $loginAttempts = $cacheItem->get();
        if ($loginAttempts >= 5) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Trop de tentatives sur l\'e-mail ' . $email . '. (5 max). Veuillez patienter (2min)'
            ], 429);
        }


        $cacheItem->set($loginAttempts + 1);
        $cacheItem->expiresAfter(120);
        $this->cache->save($cacheItem);

        return new JsonResponse([
            'error' => true,
            'message' => 'Email ou mot de passe incorrect'
        ], 400);
    }
}
