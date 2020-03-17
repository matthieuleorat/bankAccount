<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200317121536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE source_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE details_to_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE statement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, tree_root INT DEFAULT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1A977936C ON category (tree_root)');
        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE TABLE source (id INT NOT NULL, number VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE filter (id INT NOT NULL, details_to_category_id INT NOT NULL, field VARCHAR(255) NOT NULL, compare_operator VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7FC45F1D5DF6C30D ON filter (details_to_category_id)');
        $this->addSql('CREATE TABLE details_to_category (id INT NOT NULL, category_id INT NOT NULL, regex VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, debit VARCHAR(255) DEFAULT NULL, credit VARCHAR(255) DEFAULT NULL, date VARCHAR(255) NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A06FAD9012469DE2 ON details_to_category (category_id)');
        $this->addSql('CREATE TABLE transaction (id INT NOT NULL, statement INT DEFAULT NULL, date DATE NOT NULL, details TEXT NOT NULL, debit DOUBLE PRECISION DEFAULT NULL, credit DOUBLE PRECISION DEFAULT NULL, comment TEXT DEFAULT NULL, type TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_723705D1C0DB5176 ON transaction (statement)');
        $this->addSql('COMMENT ON COLUMN transaction.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transaction.type IS \'(DC2Type:object)\'');
        $this->addSql('CREATE TABLE statement (id INT NOT NULL, source_id INT NOT NULL, name VARCHAR(255) NOT NULL, starting_date DATE NOT NULL, ending_date DATE NOT NULL, starting_balance DOUBLE PRECISION DEFAULT NULL, ending_balance DOUBLE PRECISION DEFAULT NULL, total_debit DOUBLE PRECISION NOT NULL, total_credit DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0DB5176953C1C61 ON statement (source_id)');
        $this->addSql('COMMENT ON COLUMN statement.starting_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN statement.ending_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE expense (id INT NOT NULL, category_id INT DEFAULT NULL, transaction INT DEFAULT NULL, label VARCHAR(255) NOT NULL, debit DOUBLE PRECISION DEFAULT NULL, credit DOUBLE PRECISION DEFAULT NULL, date DATE NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D3A8DA612469DE2 ON expense (category_id)');
        $this->addSql('CREATE INDEX IDX_2D3A8DA6723705D1 ON expense (transaction)');
        $this->addSql('COMMENT ON COLUMN expense.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE public."user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_327C5DE7F85E0677 ON public."user" (username)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1A977936C FOREIGN KEY (tree_root) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D5DF6C30D FOREIGN KEY (details_to_category_id) REFERENCES details_to_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE details_to_category ADD CONSTRAINT FK_A06FAD9012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C0DB5176 FOREIGN KEY (statement) REFERENCES statement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statement ADD CONSTRAINT FK_C0DB5176953C1C61 FOREIGN KEY (source_id) REFERENCES source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6723705D1 FOREIGN KEY (transaction) REFERENCES transaction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1A977936C');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE details_to_category DROP CONSTRAINT FK_A06FAD9012469DE2');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA612469DE2');
        $this->addSql('ALTER TABLE statement DROP CONSTRAINT FK_C0DB5176953C1C61');
        $this->addSql('ALTER TABLE filter DROP CONSTRAINT FK_7FC45F1D5DF6C30D');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA6723705D1');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1C0DB5176');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE source_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE filter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE details_to_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transaction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE statement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE expense_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.user_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE source');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE details_to_category');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE statement');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE public."user"');
    }
}
