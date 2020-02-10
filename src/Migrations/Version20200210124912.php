<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210124912 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA62FC0CB0F');
        $this->addSql('DROP INDEX IDX_2D3A8DA62FC0CB0F ON expense');
        $this->addSql('ALTER TABLE expense CHANGE transaction_id transaction INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6723705D1 FOREIGN KEY (transaction) REFERENCES transaction (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2D3A8DA6723705D1 ON expense (transaction)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6723705D1');
        $this->addSql('DROP INDEX IDX_2D3A8DA6723705D1 ON expense');
        $this->addSql('ALTER TABLE expense CHANGE transaction transaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA62FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('CREATE INDEX IDX_2D3A8DA62FC0CB0F ON expense (transaction_id)');
    }
}
