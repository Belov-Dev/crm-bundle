<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200405144700 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE crm_entity_fields (id INT AUTO_INCREMENT NOT NULL, entity_id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, has_filtering TINYINT(1) DEFAULT NULL, show_in_datasheet TINYINT(1) DEFAULT NULL, configuration VARCHAR(255) NOT NULL, INDEX IDX_CBA5384E81257D5D (entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_menu (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, route VARCHAR(255) NOT NULL, order_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_entities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crm_entity_fields ADD CONSTRAINT FK_CBA5384E81257D5D FOREIGN KEY (entity_id) REFERENCES crm_entities (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE crm_entity_fields DROP FOREIGN KEY FK_CBA5384E81257D5D');
        $this->addSql('DROP TABLE crm_entity_fields');
        $this->addSql('DROP TABLE crm_menu');
        $this->addSql('DROP TABLE crm_entities');
    }
}
