<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Artist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Récupérer tous les artistes
        $artists = $manager->getRepository(Artist::class)->findAll();

        foreach ($artists as $artist) {
            for ($i = 0; $i < 6; $i++) {
                $album = new Album();

                // Définir l'artiste pour l'album
                $album->setArtistUserIdUser($artist);

                // Définir d'autres propriétés de l'album
                $album->setNom($faker->name());
                $album->setCover($faker->word());
                $album->setCateg($faker->word()); // Choisir une catégorie cohérente
                $album->setYear($faker->numberBetween(1900, 2023)); // Année aléatoire entre 1900 et 2023
                $manager->persist($album);
            }
        }

        $manager->flush();
    }
}
