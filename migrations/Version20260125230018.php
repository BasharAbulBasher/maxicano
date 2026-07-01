<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260125230018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE screen_werbung (id INT AUTO_INCREMENT NOT NULL, screen_id INT NOT NULL, werbung_id INT NOT NULL, INDEX IDX_2B4EECB241A67722 (screen_id), INDEX IDX_2B4EECB251AB7F9B (werbung_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE screen_werbung ADD CONSTRAINT FK_2B4EECB241A67722 FOREIGN KEY (screen_id) REFERENCES screen (id)');
        $this->addSql('ALTER TABLE screen_werbung ADD CONSTRAINT FK_2B4EECB251AB7F9B FOREIGN KEY (werbung_id) REFERENCES werbung (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screen_werbung DROP FOREIGN KEY FK_2B4EECB241A67722');
        $this->addSql('ALTER TABLE screen_werbung DROP FOREIGN KEY FK_2B4EECB251AB7F9B');
        $this->addSql('DROP TABLE screen_werbung');
    }
}
