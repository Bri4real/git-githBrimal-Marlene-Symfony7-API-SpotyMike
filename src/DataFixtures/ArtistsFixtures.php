<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ArtistsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Récupérer tous les utilisateurs
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            for ($i = 0; $i < 6; $i++) {
                $artist = new Artist();
                // Associer l'utilisateur à l'artiste
                $artist->setUserIdUser($user);
                // Générer un nom complet fictif pour l'artiste
                $artist->setFullname($faker->name());
                // Générer un label fictif pour l'artiste
                $artist->setLabel($faker->word());
                // Générer une description fictive pour l'artiste
                $artist->setDescription($faker->sentence());

                $manager->persist($artist);
            }
        }

        $manager->flush();
    }
}
