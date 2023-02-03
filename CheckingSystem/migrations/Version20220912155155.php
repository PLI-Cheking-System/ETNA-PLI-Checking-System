<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220912155155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subject ADD schedule_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A831D5E0B FOREIGN KEY (schedule_id_id) REFERENCES schedule (id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A831D5E0B ON subject (schedule_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A831D5E0B');
        $this->addSql('DROP INDEX IDX_FBCE3E7A831D5E0B ON subject');
        $this->addSql('ALTER TABLE subject DROP schedule_id_id');
    }
}
