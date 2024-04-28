<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->repository = $entityManager->getRepository(User::class);
    }

    #[Route('/register', name: 'app_create_user', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHash): JsonResponse
    {
        $requestData = $this->parseRequestData($request);

        // Vérification des champs obligatoires
        $missingFields = $this->checkRequiredFields($requestData);
        if (!empty($missingFields)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Des champs obligatoires sont manquants.',
                'status' => 'Donnée manquante',
                'missing_fields' => $missingFields,
            ], 400);
        }

        // Vérification de l'existence de l'utilisateur avec l'email fourni
        $existingUser = $this->repository->findOneBy(['email' => $requestData['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Cet email est déjà utilisé par un autre compte.',
                'status' => "Email dejà utilisé",
            ], 409);
        }


        $dateBirth = $this->checkDateFormat($requestData['dateBirth']);
        if (!$dateBirth) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de la date de naissance est invalide. Le format attendu est JJ/MM/AAAA.',
                'status' => "Format de date de naissance invalide"
            ], 400);
        }

        $age = $this->checkAge($dateBirth);
        if ($age < 12) {
            return new JsonResponse([
                'error' => true,
                'message' => 'L\'utilisateur doit avoir au moins 12 ans.',
                'status' => "Age minimum non respecté"
            ], 400);
        }


        $tel = $requestData['tel'] ?? null;
        if ($tel !== null && !$this->checkPhoneNumber($tel)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format du numéro de téléphone est invalide.',
                'status' => "Format de téléphone invalide"
            ], 400);
        }


        $password = $requestData['password'] ?? null;
        if (!$this->checkPassword($password)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe ne respecte pas les critères de sécurité.',
                'status' => "Format de mot de passe invalide"
            ], 400);
        }

        // Validation du format de l'email
        $email = $requestData['email'] ?? null;
        if (!$this->checkEmail($email)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le format de l\'email est invalide.',
                'status' => 'Format d\'email invalide'
            ], 400);
        }

        $sexe = $requestData['sexe'] ?? null;
        if ($sexe !== null && ($sexe !== 0 && $sexe !== 1)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.',
                'status' => "Valeur de sexe invalide"
            ], 400);
        }




        $notValid = $this->checkName($requestData);
        if (!empty($notValid)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le nom et le prénom doit obligatoirement être supérieur à 1 caractère et inférieur à 60 caractères.',
                'status' => "Une ou plusieurs données sont erronées.",
                'invalid_data' => $notValid
            ],);
        }

        $user = new User();
        $hashedPassword = $passwordHash->hashPassword($user, $password);
        $currentTime = new DateTimeImmutable();

        $user->setFirstname($requestData['firstname'])
            ->setLastname($requestData['lastname'])
            ->setEmail($email)
            ->setSexe($sexe)
            ->setPassword($hashedPassword)
            ->setTel($tel)
            ->setDateBirth($dateBirth)
            ->setActive('Actif')
            ->setIdUser(Uuid::v1())
            ->setCreatedAt($currentTime)
            ->setUpdateAt($currentTime);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'error' => false,
            'message' => "L'utilisateur a bien été créé avec succès.",
            'user' => $user->registerSerializer(),
        ], 201);
    }

    private function parseRequestData(Request $request): array
    {
        $requestData = $request->request->all();

        if ($request->headers->get('content-type') === 'application/json') {
            $requestData = json_decode($request->getContent(), true);
        }

        return $requestData;
    }

    private function checkRequiredFields(array $requestData): array
    {
        $requiredFields = ['firstname', 'lastname', 'email', 'password', 'dateBirth'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }

    private function checkDateFormat(string $dateString): ?DateTimeImmutable
    {
        $dateBirth = DateTimeImmutable::createFromFormat('d/m/Y', $dateString);
        return $dateBirth ? $dateBirth : null;
    }

    private function checkAge(DateTimeImmutable $dateBirth): int
    {
        $today = new DateTime();
        return $today->diff($dateBirth)->y;
    }

    private function checkPhoneNumber(?string $phoneNumber): bool
    {
        return preg_match('/^0[1-9][0-9]{8}$|^01[0-9]{8}$/', $phoneNumber);
    }


    private function checkPassword(?string $password): bool
    {
        $passRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($passRegex, $password);
    }


    private function checkEmail(?string $email): bool
    {
        if ($email === null) {
            return true;
        }
        $regex = '/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/';
        return preg_match($regex, $email) === 1;
    }


    private function checkName(array $requestData): array
    {
        $invalidData = [];

        if (isset($requestData['firstname'])) {
            $firstname = $requestData['firstname'];
            if (!preg_match('/^[a-zA-Z\s]+$/', $firstname) || strlen($firstname) > 60 || strlen($firstname) < 1) {
                $invalidData[] = 'firstname';
            }
        }

        if (isset($requestData['lastname'])) {
            $lastname = $requestData['lastname'];
            if (!preg_match('/^[a-zA-Z\s]+$/', $lastname) || strlen($lastname) > 60 || strlen($lastname) < 1) {
                $invalidData[] = 'lastname';
            }
        }

        return $invalidData;
    }
}
