<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200328140820 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE debt_expense (debt_id INT NOT NULL, expense_id INT NOT NULL, PRIMARY KEY(debt_id, expense_id))');
        $this->addSql('CREATE INDEX IDX_350F5E44240326A5 ON debt_expense (debt_id)');
        $this->addSql('CREATE INDEX IDX_350F5E44F395DB7B ON debt_expense (expense_id)');
        $this->addSql('ALTER TABLE debt_expense ADD CONSTRAINT FK_350F5E44240326A5 FOREIGN KEY (debt_id) REFERENCES debt (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE debt_expense ADD CONSTRAINT FK_350F5E44F395DB7B FOREIGN KEY (expense_id) REFERENCES expense (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE debt ADD date DATE NOT NULL');
        $this->addSql('COMMENT ON COLUMN debt.date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE debt_expense');
        $this->addSql('ALTER TABLE debt DROP date');
    }
}
