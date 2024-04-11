<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données obligatoires
        $requiredFields = ['firstname', 'lastname', 'email', 'password', 'dateBirth'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Une ou plusieurs données obligatoire sont manquantes'
                ], 400);
            }
        }

        // Vérification de l'âge
        $dateBirth = new \DateTime($data['dateBirth']);
        $now = new \DateTime();
        $age = $now->diff($dateBirth)->y;
        if ($age < 18) {
            return new JsonResponse([
                'error' => true,
                'message' => 'L\'âge de l\'utilisateur ne permet pas (12)'
            ], 406);
        }

        // Vérification si un compte avec cet e-mail existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Un compte utilisant cette adresse e-mail est déjà enregistré'
            ], 409);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);
        $user->setDateBirth($dateBirth);
        $user->setCreateAt(new \DateTimeImmutable());

        // Données facultatives
        if (isset($data['tel'])) {
            $user->setTel($data['tel']);
        }
        if (isset($data['sexe'])) {
            $user->setSexe($data['sexe']);
        }

        // Hashage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Enregistrement de l'utilisateur
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Réponse de succès
        return new JsonResponse([
            'error' => false,
            'message' => 'L\'utilisateur a bien été créé avec succès',
            'user' => [
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'tel' => $user->getTel(),
                'sexe' => $user->getSexe(),
                'dateBirth' => $user->getDateBirth()->format('d-m-Y'),
                'createdAt' => $user->getCreateAt()->format('d-m-Y H:i:s'),
                'updateAt' => $user->getUpdateAt() ? $user->getUpdateAt()->format('d-m-Y H:i:s') : null,
            ]
        ], 201);
    }
}
