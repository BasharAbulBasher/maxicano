<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106213613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE acticle_screen (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, screen_id INT NOT NULL, INDEX IDX_DC1A89EF7294869C (article_id), INDEX IDX_DC1A89EF41A67722 (screen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acticle_screen ADD CONSTRAINT FK_DC1A89EF7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE acticle_screen ADD CONSTRAINT FK_DC1A89EF41A67722 FOREIGN KEY (screen_id) REFERENCES screen (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE acticle_screen DROP FOREIGN KEY FK_DC1A89EF7294869C');
        $this->addSql('ALTER TABLE acticle_screen DROP FOREIGN KEY FK_DC1A89EF41A67722');
        $this->addSql('DROP TABLE acticle_screen');
    }
}
