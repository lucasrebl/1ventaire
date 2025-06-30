<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630144613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B6459EEA759');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B645A2B28FE8');
        $this->addSql('DROP TABLE receipt');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE receipt (id INT AUTO_INCREMENT NOT NULL, inventory_id INT NOT NULL, uploaded_by_id INT NOT NULL, filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, uploaded_at DATETIME NOT NULL, raw_data LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, processed_at DATETIME DEFAULT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pending\' NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5399B645A2B28FE8 (uploaded_by_id), INDEX IDX_5399B6459EEA759 (inventory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B6459EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B645A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
