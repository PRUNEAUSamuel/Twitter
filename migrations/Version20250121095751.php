<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121095751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contents (id INT AUTO_INCREMENT NOT NULL, tweet_id INT DEFAULT NULL, content LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_B4FA11771041E39B (tweet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contents ADD CONSTRAINT FK_B4FA11771041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contents DROP FOREIGN KEY FK_B4FA11771041E39B');
        $this->addSql('DROP TABLE contents');
    }
}
