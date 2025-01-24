<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123144818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tweets DROP FOREIGN KEY FK_AA38402584A0A3ED');
        $this->addSql('ALTER TABLE contents DROP FOREIGN KEY FK_B4FA11771041E39B');
        $this->addSql('DROP TABLE contents');
        $this->addSql('DROP INDEX UNIQ_AA38402584A0A3ED ON tweets');
        $this->addSql('ALTER TABLE tweets ADD content VARCHAR(255) NOT NULL, DROP content_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contents (id INT AUTO_INCREMENT NOT NULL, tweet_id INT DEFAULT NULL, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_B4FA11771041E39B (tweet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contents ADD CONSTRAINT FK_B4FA11771041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id)');
        $this->addSql('ALTER TABLE tweets ADD content_id INT DEFAULT NULL, DROP content');
        $this->addSql('ALTER TABLE tweets ADD CONSTRAINT FK_AA38402584A0A3ED FOREIGN KEY (content_id) REFERENCES contents (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AA38402584A0A3ED ON tweets (content_id)');
    }
}
