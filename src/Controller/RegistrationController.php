<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use DateTimeImmutable;
use DateTime;

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
        $requestData = $request->request->all();

        $validationResult = $this->validateRegData($requestData);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $user = $this->createUser($requestData);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'error' => false,
            'message' => 'L\'utilisateur a bien été créé avec succès',
            'user' => $this->getUserData($user)
        ], 201);
    }

    private function validateRegData(array $data): JsonResponse|bool
    {
        $requiredFields = ['firstname', 'lastname', 'email', 'password', 'dateBirth'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Des champs obligatoires sont manquants.',
                    'status' => 'Donnée manquante'
                ], 400);
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'e-mail est invalide',
                'status' => 'Format d\'e-mail invalide'
            ], 400);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $data['password'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial, et avoir au moins 8 caractères',
                'status' => 'Format de mot de passe invalide'
            ], 400);
        }

        if (!DateTime::createFromFormat('d/m/Y', $data['dateBirth'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de la date de naissance est invalide. Le format attendu est JJ/MM/AAAA',
                'status' => 'Format de date de naissance invalide'
            ], 400);
        }

        $dateBirth = DateTime::createFromFormat('d/m/Y', $data['dateBirth']);
        $now = new DateTime();
        $age = $now->diff($dateBirth)->y;
        if ($age < 12) {
            return new JsonResponse([
                'error' => true,
                'message' => "L'utilisateur doit avoir au moins 12 ans.",
                'status' => 'Âge minimum non respecté (moins de 12 ans)'
            ], 400);
        }

        if (!empty($data['tel']) && !preg_match('/^\d{10}$/', $data['tel'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format du numéro de téléphone est invalide',
                'status' => 'Format de téléphone invalide'
            ], 400);
        }

        if (!empty($data['sexe']) && !in_array($data['sexe'], [0, 1])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour femme, 1 pour homme',
                'status' => 'Valeur de sexe invalide'
            ], 400);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Cet e-mail est déjà utilisé par un autre compte',
                'status' => 'Email déjà utilisé'
            ], 409);
        }

        return true;
    }

    private function createUser(array $data): User
    {
        $user = new User();
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setDateBirth(DateTime::createFromFormat('d/m/Y', $data['dateBirth']));
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdateAt(new DateTimeImmutable());
        $user->setIdUser(Uuid::v1());

        if (!empty($data['tel'])) {
            $user->setTel($data['tel']);
        }

        if (!empty($data['sexe'])) {
            $user->setSexe((int)$data['sexe']);
        }

        return $user;
    }

    private function getUserData(User $user): array
    {
        return [
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'tel' => $user->getTel(),
            'sexe' => $user->getSexe(),
            'dateBirth' => $user->getDateBirth()->format('d-m-Y'),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
            'updateAt' => $user->getUpdateAt() ? $user->getUpdateAt()->format('Y-m-d') : null,
        ];
    }
}
