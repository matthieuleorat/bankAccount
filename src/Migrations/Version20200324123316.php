<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324123316 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE source ADD default_budget_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE source ADD CONSTRAINT FK_5F8A7F73A7C98E1F FOREIGN KEY (default_budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5F8A7F73A7C98E1F ON source (default_budget_id)');
        $this->addSql('ALTER TABLE transaction ALTER type TYPE TEXT');
        $this->addSql('ALTER TABLE transaction ALTER type DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transaction.type IS NULL');
        $this->addSql('ALTER TABLE expense ADD budget_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA636ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2D3A8DA636ABA6B8 ON expense (budget_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE source DROP CONSTRAINT FK_5F8A7F73A7C98E1F');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA636ABA6B8');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP INDEX IDX_5F8A7F73A7C98E1F');
        $this->addSql('ALTER TABLE source DROP default_budget_id');
        $this->addSql('ALTER TABLE transaction ALTER type TYPE TEXT');
        $this->addSql('ALTER TABLE transaction ALTER type DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transaction.type IS \'(DC2Type:object)\'');
        $this->addSql('DROP INDEX IDX_2D3A8DA636ABA6B8');
        $this->addSql('ALTER TABLE expense DROP budget_id');
    }
}
