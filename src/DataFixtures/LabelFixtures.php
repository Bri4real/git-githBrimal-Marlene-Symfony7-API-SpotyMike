<?php

namespace App\DataFixtures;

use App\Entity\Label;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LabelFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $label = new Label();
            $label->setIdLabel('spotimike:label:' . $faker->uuid); // Génération d'un identifiant unique pour le label
            $label->setLabelName($faker->company); // Utilisation de Faker pour générer un nom de label aléatoire

            $manager->persist($label);
        }

        $manager->flush();
    }
}
