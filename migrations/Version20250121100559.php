<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121100559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, tweet_id INT DEFAULT NULL, INDEX IDX_49CA4E7DA76ED395 (user_id), INDEX IDX_49CA4E7D1041E39B (tweet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D1041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id)');
        $this->addSql('ALTER TABLE tweets DROP FOREIGN KEY FK_AA3840259D86650F');
        $this->addSql('DROP INDEX IDX_AA3840259D86650F ON tweets');
        $this->addSql('ALTER TABLE tweets CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE tweets ADD CONSTRAINT FK_AA384025A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_AA384025A76ED395 ON tweets (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D1041E39B');
        $this->addSql('DROP TABLE likes');
        $this->addSql('ALTER TABLE tweets DROP FOREIGN KEY FK_AA384025A76ED395');
        $this->addSql('DROP INDEX IDX_AA384025A76ED395 ON tweets');
        $this->addSql('ALTER TABLE tweets CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE tweets ADD CONSTRAINT FK_AA3840259D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_AA3840259D86650F ON tweets (user_id_id)');
    }
}
