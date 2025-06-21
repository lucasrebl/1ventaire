<?php

namespace App\DataFixtures;

use App\Entity\Emplacement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EmplacementFixtures extends Fixture
{
    public const EMPLACEMENT_CUISINE = 'emplacement-cuisine';
    public const EMPLACEMENT_SALON = 'emplacement-salon';
    public const EMPLACEMENT_CHAMBRE = 'emplacement-chambre';
    public const EMPLACEMENT_GRENIER = 'emplacement-grenier';
    public const EMPLACEMENT_CAVE = 'emplacement-cave';
    public const EMPLACEMENT_GARAGE = 'emplacement-garage';

    public function load(ObjectManager $manager): void
    {
        $emplacements = [
            [self::EMPLACEMENT_CUISINE, 'Cuisine'],
            [self::EMPLACEMENT_SALON, 'Salon'],
            [self::EMPLACEMENT_CHAMBRE, 'Chambre'],
            [self::EMPLACEMENT_GRENIER, 'Grenier'],
            [self::EMPLACEMENT_CAVE, 'Cave'],
            [self::EMPLACEMENT_GARAGE, 'Garage'],
        ];

        foreach ($emplacements as [$reference, $libelle]) {
            $emplacement = new Emplacement();
            $emplacement->setLibelle($libelle);

            $manager->persist($emplacement);
            $this->addReference($reference, $emplacement);
        }

        $manager->flush();
    }
}
