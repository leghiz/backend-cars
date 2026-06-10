<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260607084429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request DROP CONSTRAINT fk_3b978f9ff64382e3');
        $this->addSql('DROP INDEX uniq_3b978f9ff64382e3');
        $this->addSql('ALTER TABLE request RENAME COLUMN car_model_id TO lot_id');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FA8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B978F9FA8CBA5F7 ON request (lot_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request DROP CONSTRAINT FK_3B978F9FA8CBA5F7');
        $this->addSql('DROP INDEX UNIQ_3B978F9FA8CBA5F7');
        $this->addSql('ALTER TABLE request RENAME COLUMN lot_id TO car_model_id');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT fk_3b978f9ff64382e3 FOREIGN KEY (car_model_id) REFERENCES car_model (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_3b978f9ff64382e3 ON request (car_model_id)');
    }
}
