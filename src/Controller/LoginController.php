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
            return new JsonResponse([
                'error' => true,
                'message' => 'Email / password manquant',
                'status' => 'Donnée manquante',
                'code' => 400
            ]);
        }

        // Récupération de l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérification de l'utilisateur
        if (!$user) {
            return $this->handleLoginFailure($email);
        }

        // Vérification du format du mot de passe
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial et doit avoir au moins 8 caractères.',
                'status' => 'Le mot de passe ne respecte pas les critères',
                'code' => 400
            ]);
        }

        // Vérification de l'existence de l'utilisateur
        if (!$user->isActive()) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le compte n’est plus actif ou suspendu',
                'status' => 'Compte non actif ou suspendu',
                'code' => 403
            ]);
        }

        // Vérification du nombre de tentatives de connexion
        $cacheKeyAttempts = 'login_attempts_' . md5($email);
        $cacheItemAttempts = $this->cache->getItem($cacheKeyAttempts);
        $loginAttempts = $cacheItemAttempts->get();
        if ($loginAttempts >= 5) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Trop de tentatives de connexion (5 max). Réessayez dans 5 minutes.',
                'status' => 'Trop de tentatives',
                'code' => 429
            ]);
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l’e-mail est invalide.',
                'status' => 'Format d’email invalide',
                'code' => 400
            ]);
        }


        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->handleLoginFailure($email);
        }


        $token = $this->JWTManager->create($user);

        $userSexe = $user->getSexe();

        if ($userSexe === 0) {
            $sexeFormatted = "Femme";
        } elseif ($userSexe === 1) {
            $sexeFormatted = "Homme";
        } else {
            $sexeFormatted = null;
        }


        return new JsonResponse([
            'error' => false,
            'message' => 'L\'utilisateur a été authentifié avec succès',
            'status' => 'Success',
            'code' => 200,
            'user' => [
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'tel' => $user->getTel(),
                'sexe' => $sexeFormatted,
                'artist' => $user->getArtist(),
                'dateBirth' => $user->getDateBirth()->format('m-d-Y'),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
            'token' => $token,
        ]);
    }

    private function handleLoginFailure(string $email): JsonResponse
    {
        // Incrémenter le compteur de tentatives de connexion
        $cacheKeyAttempts = 'login_attempts_' . md5($email);
        $cacheItemAttempts = $this->cache->getItem($cacheKeyAttempts);
        $loginAttempts = $cacheItemAttempts->get();
        $cacheItemAttempts->set($loginAttempts + 1);
        $cacheItemAttempts->expiresAfter(300); // 5 minutes
        $this->cache->save($cacheItemAttempts);


        $cacheKeyError = 'login_error_' . md5($email);
        $cacheItemError = $this->cache->getItem($cacheKeyError);
        $cacheItemError->set(true);
        $cacheItemError->expiresAfter(300); // 5 minutes
        $this->cache->save($cacheItemError);


        return new JsonResponse([
            'error' => true,
            'message' => "Echec d'authentification",
            'status' => 'Échec de connexion',
            'code' => 401
        ]);
    }
}
