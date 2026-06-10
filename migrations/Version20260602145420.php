<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602145420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_user DROP CONSTRAINT fk_f234f1b3427eb8a5');
        $this->addSql('ALTER TABLE request_user DROP CONSTRAINT fk_f234f1b3a76ed395');
        $this->addSql('DROP TABLE request_user');
        $this->addSql('ALTER TABLE request ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F9B6B5FBA FOREIGN KEY (account_id) REFERENCES "user" (id)');
        $this->addSql('CREATE INDEX IDX_3B978F9F9B6B5FBA ON request (account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request_user (request_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (request_id, user_id))');
        $this->addSql('CREATE INDEX idx_f234f1b3a76ed395 ON request_user (user_id)');
        $this->addSql('CREATE INDEX idx_f234f1b3427eb8a5 ON request_user (request_id)');
        $this->addSql('ALTER TABLE request_user ADD CONSTRAINT fk_f234f1b3427eb8a5 FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE request_user ADD CONSTRAINT fk_f234f1b3a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE request DROP CONSTRAINT FK_3B978F9F9B6B5FBA');
        $this->addSql('DROP INDEX IDX_3B978F9F9B6B5FBA');
        $this->addSql('ALTER TABLE request DROP account_id');
    }
}
