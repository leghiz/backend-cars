<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609061535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE verification_code DROP CONSTRAINT fk_e821c39f9b6b5fba');
        $this->addSql('ALTER TABLE verification_code ADD CONSTRAINT FK_E821C39F9B6B5FBA FOREIGN KEY (account_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE verification_code DROP CONSTRAINT FK_E821C39F9B6B5FBA');
        $this->addSql('ALTER TABLE verification_code ADD CONSTRAINT fk_e821c39f9b6b5fba FOREIGN KEY (account_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
