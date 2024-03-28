<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Regex;

class SignUpController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/signup', name: 'app_signup', methods: ['POST'])]
    public function signUp(Request $request): JsonResponse
    {
        // Obtenir les données de la requête
        $requestData = $request->request->all();

        // Vérifier si les clés existent avant d'y accéder
        if (!isset($requestData['tel']) || !isset($requestData['name']) || !isset($requestData['email']) || !isset($requestData['idUser']) || !isset($requestData['encrypte'])) {
            return $this->json(['error' => 'Veuillez remplir tous les champs '], 400);
        }
        

        // Validation du numéro de téléphone
        $phoneNumber = $requestData['tel'];
        $violations = $this->validator->validate($phoneNumber, [
            new Regex([
                'pattern' => '/^\+33(?:0|\d{1})\d{9}$/',
                'message' => 'Numero de telephone invalid.Veuillez inserer un numero francais ',
            ]),
            
        ]);

        if (count($violations) > 0) {
            return $this->json(['error' => (string) $violations[0]->getMessage()], 400);
        }


        // Création d'un nouvel utilisateur
        $user = new User();
        $user->setName($requestData['name']);
        $user->setEmail($requestData['email']);
        $user->setIdUser($requestData['idUser']);
        $user->setEncrypte($requestData['encrypte']);
        $user->setTel($phoneNumber); // Utilisation du numéro de téléphone valide
        $user->setCreateAt(new \DateTimeImmutable());
        $user->setUpdateAt(new \DateTime());

        // Enregistrement de l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Inscription effectuée avec succès',
            'user' => $user,
        ]);
    }

   
}
