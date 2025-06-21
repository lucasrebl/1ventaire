<?php

namespace App\DataFixtures;

use App\Entity\Inventaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InventaireFixtures extends Fixture
{
    public const INVENTAIRE_MAISON = 'inventaire-maison';
    public const INVENTAIRE_BUREAU = 'inventaire-bureau';
    public const INVENTAIRE_VACANCES = 'inventaire-vacances';

    public function load(ObjectManager $manager): void
    {
        $inventaires = [
            [self::INVENTAIRE_MAISON, 'Inventaire Maison'],
            [self::INVENTAIRE_BUREAU, 'Inventaire Bureau'],
            [self::INVENTAIRE_VACANCES, 'Inventaire Maison de vacances'],
        ];

        foreach ($inventaires as [$reference, $nom]) {
            $inventaire = new Inventaire();
            $inventaire->setNom($nom);

            $manager->persist($inventaire);
            $this->addReference($reference, $inventaire);
        }

        $manager->flush();
    }
}
