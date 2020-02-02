<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200202150445 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1849CB65B');
        $this->addSql('DROP INDEX IDX_723705D1849CB65B ON transaction');
        $this->addSql('ALTER TABLE transaction CHANGE statement_id statement INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C0DB5176 FOREIGN KEY (statement) REFERENCES statement (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_723705D1C0DB5176 ON transaction (statement)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1C0DB5176');
        $this->addSql('DROP INDEX IDX_723705D1C0DB5176 ON transaction');
        $this->addSql('ALTER TABLE transaction CHANGE statement statement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1849CB65B FOREIGN KEY (statement_id) REFERENCES statement (id)');
        $this->addSql('CREATE INDEX IDX_723705D1849CB65B ON transaction (statement_id)');
    }
}
