<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250628181936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shared_inventory (id INT AUTO_INCREMENT NOT NULL, inventory_id INT NOT NULL, shared_with_id INT NOT NULL, access_level VARCHAR(20) NOT NULL, shared_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_131F1FB59EEA759 (inventory_id), INDEX IDX_131F1FB5D14FE63F (shared_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shared_inventory ADD CONSTRAINT FK_131F1FB59EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE shared_inventory ADD CONSTRAINT FK_131F1FB5D14FE63F FOREIGN KEY (shared_with_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shared_inventory DROP FOREIGN KEY FK_131F1FB59EEA759');
        $this->addSql('ALTER TABLE shared_inventory DROP FOREIGN KEY FK_131F1FB5D14FE63F');
        $this->addSql('DROP TABLE shared_inventory');
    }
}
