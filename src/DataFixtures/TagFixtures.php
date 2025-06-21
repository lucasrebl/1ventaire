<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public const TAG_IMPORTANT = 'tag-important';
    public const TAG_FRAGILE = 'tag-fragile';
    public const TAG_ARCHIVE = 'tag-archive';
    public const TAG_NEUF = 'tag-neuf';
    public const TAG_PRECIEUX = 'tag-precieux';
    public const TAG_VINTAGE = 'tag-vintage';

    public function load(ObjectManager $manager): void
    {
        $tags = [
            [self::TAG_IMPORTANT, 'Important', '#FF0000'], // Rouge
            [self::TAG_FRAGILE, 'Fragile', '#FFA500'],    // Orange
            [self::TAG_ARCHIVE, 'Archive', '#808080'],    // Gris
            [self::TAG_NEUF, 'Neuf', '#00FF00'],         // Vert
            [self::TAG_PRECIEUX, 'Précieux', '#FFD700'], // Or
            [self::TAG_VINTAGE, 'Vintage', '#8B4513'],   // Marron
        ];

        foreach ($tags as [$reference, $nom, $color]) {
            $tag = new Tag();
            $tag->setNom($nom);
            $tag->setColor($color);

            $manager->persist($tag);
            $this->addReference($reference, $tag);
        }

        $manager->flush();
    }
}
