<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Emplacement;
use App\Entity\Inventaire;
use App\Entity\Tag;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->createArticlesMaison($manager);
        $this->createArticlesBureau($manager);
        $this->createArticlesVacances($manager);

        $manager->flush();
    }

    private function createArticlesMaison(ObjectManager $manager): void
    {
        // Articles pour la maison
        $articlesData = [
            [
                'libelle' => 'Télévision Samsung',
                'description' => 'Téléviseur 4K 55 pouces acheté en 2024',
                'date_limite' => new \DateTime('2034-06-21'),
                'type' => TypeFixtures::TYPE_ELECTRONIQUE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_NEUF, TagFixtures::TAG_PRECIEUX]
            ],
            [
                'libelle' => 'Robot de cuisine',
                'description' => 'Robot multifonction Thermomix',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_CUISINE,
                'tags' => [TagFixtures::TAG_IMPORTANT]
            ],
            [
                'libelle' => 'Canapé cuir',
                'description' => 'Canapé 3 places en cuir marron',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_MEUBLE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_VINTAGE]
            ],
            [
                'libelle' => 'Collection livres Harry Potter',
                'description' => 'Collection complète des 7 tomes',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_LIVRE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_PRECIEUX]
            ],
            [
                'libelle' => 'Veste d\'hiver',
                'description' => 'Veste d\'hiver imperméable taille L',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_VETEMENT,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_CHAMBRE,
                'tags' => []
            ],
        ];

        $this->createArticles($manager, $articlesData, InventaireFixtures::INVENTAIRE_MAISON);
    }

    private function createArticlesBureau(ObjectManager $manager): void
    {
        // Articles pour le bureau
        $articlesData = [
            [
                'libelle' => 'Ordinateur portable Dell',
                'description' => 'Ordinateur portable Dell XPS 15, 32Go RAM, 1TB SSD',
                'date_limite' => new \DateTime('2027-03-15'),
                'type' => TypeFixtures::TYPE_ELECTRONIQUE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_IMPORTANT, TagFixtures::TAG_PRECIEUX]
            ],
            [
                'libelle' => 'Écran Dell 27 pouces',
                'description' => 'Écran 4K Dell Ultrasharp',
                'date_limite' => new \DateTime('2028-01-10'),
                'type' => TypeFixtures::TYPE_ELECTRONIQUE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_IMPORTANT]
            ],
            [
                'libelle' => 'Bureau ajustable',
                'description' => 'Bureau électrique ajustable en hauteur',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_MEUBLE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => []
            ],
            [
                'libelle' => 'Documentation technique',
                'description' => 'Ensemble de manuels pour les équipements',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_LIVRE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => [TagFixtures::TAG_ARCHIVE]
            ],
        ];

        $this->createArticles($manager, $articlesData, InventaireFixtures::INVENTAIRE_BUREAU);
    }

    private function createArticlesVacances(ObjectManager $manager): void
    {
        // Articles pour la maison de vacances
        $articlesData = [
            [
                'libelle' => 'Barbecue Weber',
                'description' => 'Barbecue à charbon modèle premium',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_GARAGE,
                'tags' => []
            ],
            [
                'libelle' => 'Set de plage',
                'description' => 'Parasol, chaises et jeux de plage',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_GARAGE,
                'tags' => [TagFixtures::TAG_FRAGILE]
            ],
            [
                'libelle' => 'Outils de jardinage',
                'description' => 'Set complet avec tondeuse, taille-haie, etc.',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_GARAGE,
                'tags' => []
            ],
            [
                'libelle' => 'Vélos',
                'description' => '2 vélos adultes et 2 vélos enfants',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_GARAGE,
                'tags' => [TagFixtures::TAG_IMPORTANT]
            ],
            [
                'libelle' => 'Collection de romans d\'été',
                'description' => 'Collection de romans pour les vacances',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_LIVRE,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_SALON,
                'tags' => []
            ],
            [
                'libelle' => 'Équipement de randonnée',
                'description' => 'Sacs, bâtons, gourdes, etc.',
                'date_limite' => null,
                'type' => TypeFixtures::TYPE_OBJET,
                'emplacement' => EmplacementFixtures::EMPLACEMENT_CAVE,
                'tags' => [TagFixtures::TAG_IMPORTANT]
            ],
        ];

        $this->createArticles($manager, $articlesData, InventaireFixtures::INVENTAIRE_VACANCES);
    }

    private function createArticles(ObjectManager $manager, array $articlesData, string $inventaireReference): void
    {
        foreach ($articlesData as $articleData) {
            $article = new Article();
            $article->setLibelle($articleData['libelle']);
            $article->setDescription($articleData['description']);
            $article->setDateLimite($articleData['date_limite']);

            // Relations
            $article->setType($this->getReference($articleData['type'], Type::class));
            $article->setEmplacement($this->getReference($articleData['emplacement'], Emplacement::class));
            $article->setInventaire($this->getReference($inventaireReference, Inventaire::class));

            // Tags
            foreach ($articleData['tags'] as $tagReference) {
                $article->addTag($this->getReference($tagReference, Tag::class));
            }

            $manager->persist($article);
        }
    }

    public function getDependencies(): array
    {
        return [
            TypeFixtures::class,
            EmplacementFixtures::class,
            InventaireFixtures::class,
            TagFixtures::class,
        ];
    }
}
