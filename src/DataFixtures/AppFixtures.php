<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Provider\PhoneNumber;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $faker->addProvider(new PhoneNumber($faker));

        for ($i = 0; $i < 6; $i++) {
            $user = new User();
            $user->setIdUser($faker->uuid);
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setEmail($faker->email);
            $user->setDateBirth($faker->dateTimeBetween("-40 years", "-18 years"));
            $user->setCreateAt(new \DateTimeImmutable());

            // Ajouter sexe (homme ou femme)
            $user->setSexe($faker->randomElement(['Homme', 'Femme']));

            // Générer un numéro de téléphone français au format spécifique
            $phoneNumber = '+33 ' . substr($faker->phoneNumber, 1);
            $user->settel($phoneNumber);

            // Générer un mot de passe aléatoire pour chaque utilisateur
            $password = 'password123'; // Utilisation du même mot de passe pour la démo
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
