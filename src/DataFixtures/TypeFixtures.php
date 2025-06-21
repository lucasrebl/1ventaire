<?php

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture
{
    public const TYPE_OBJET = 'type-objet';
    public const TYPE_LIVRE = 'type-livre';
    public const TYPE_VETEMENT = 'type-vetement';
    public const TYPE_ELECTRONIQUE = 'type-electronique';
    public const TYPE_MEUBLE = 'type-meuble';

    public function load(ObjectManager $manager): void
    {
        $types = [
            [self::TYPE_OBJET, 'Objet'],
            [self::TYPE_LIVRE, 'Livre'],
            [self::TYPE_VETEMENT, 'Vêtement'],
            [self::TYPE_ELECTRONIQUE, 'Électronique'],
            [self::TYPE_MEUBLE, 'Meuble'],
        ];

        foreach ($types as [$reference, $libelle]) {
            $type = new Type();
            $type->setLibelle($libelle);

            $manager->persist($type);
            $this->addReference($reference, $type);
        }

        $manager->flush();
    }
}
