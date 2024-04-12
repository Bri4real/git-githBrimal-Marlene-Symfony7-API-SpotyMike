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
        // Récupération des données du formulaire encodées
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $dateBirth = $request->request->get('dateBirth');
        $tel = $request->request->get('tel');
        $sexe = $request->request->get('sexe');
        $idUser = Uuid::v1();


        // Affichage des données récupérées pour le débogage
        //    var_dump(compact('firstname', 'lastname', 'email', 'password', 'dateBirth', 'tel', 'sexe'));

        // Vérification des données obligatoires
        $requiredFields = ['firstname', 'lastname', 'email', 'password', 'dateBirth'];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Une ou plusieurs données obligatoires sont manquantes'
                ], 400);
            }
        }

        // Vérification de l'âge
        $dateBirth = new \DateTime($dateBirth);
        $now = new \DateTime();
        $age = $now->diff($dateBirth)->y;
        if ($age < 18) {
            return new JsonResponse([
                'error' => true,
                'message' => 'L\'âge de l\'utilisateur ne permet pas (12)'
            ], 406);
        }

        // Vérification si un compte avec cet e-mail existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Un compte utilisant cette adresse e-mail est déjà enregistré'
            ], 409);
        }

        $now = new \DateTimeImmutable();
        // Création de l'utilisateur
        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setDateBirth($dateBirth);
        $user->setCreatedAt($now);
        $user->setUpdateAt($now);
        $user->setIdUser($idUser);



        // Données facultatives
        if ($tel) {
            $user->setTel($tel);
        }
        if ($sexe !== null) {
            // Convertir la valeur du sexe en int pour utiliser la méthode setSexe
            $sexe = (int)$sexe;
            $user->setSexe($sexe);
        }

        // Hashage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
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
                'sexe' => $user->getFormattedSexe(),
                'dateBirth' => $user->getDateBirth()->format('d-m-Y'),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
                'updateAt' => $user->getUpdateAt() ? $user->getUpdateAt()->format('Y-m-d') : null,
            ]
        ], 201);
    }
}
