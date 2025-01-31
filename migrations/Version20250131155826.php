<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131155826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1041E39B');
        $this->addSql('ALTER TABLE comment CHANGE tweet_id tweet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE retweet DROP FOREIGN KEY FK_45E67DB31041E39B');
        $this->addSql('ALTER TABLE retweet ADD CONSTRAINT FK_45E67DB31041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1041E39B');
        $this->addSql('ALTER TABLE comment CHANGE tweet_id tweet_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id)');
        $this->addSql('ALTER TABLE retweet DROP FOREIGN KEY FK_45E67DB31041E39B');
        $this->addSql('ALTER TABLE retweet ADD CONSTRAINT FK_45E67DB31041E39B FOREIGN KEY (tweet_id) REFERENCES tweets (id)');
    }
}
