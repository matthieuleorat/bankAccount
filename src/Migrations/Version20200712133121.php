<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200712133121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE filter_id_seq CASCADE');
        $this->addSql('DROP TABLE filter');
        $this->addSql('ALTER INDEX idx_7fc45f1d5df6c30d RENAME TO IDX_B61F9B815DF6C30D');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE filter (id INT NOT NULL, details_to_category_id INT NOT NULL, field VARCHAR(255) NOT NULL, compare_operator VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL)');
        $this->addSql('ALTER INDEX idx_b61f9b815df6c30d RENAME TO idx_7fc45f1d5df6c30d');
    }
}
