<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121100137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tweets ADD content_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tweets ADD CONSTRAINT FK_AA38402584A0A3ED FOREIGN KEY (content_id) REFERENCES contents (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AA38402584A0A3ED ON tweets (content_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tweets DROP FOREIGN KEY FK_AA38402584A0A3ED');
        $this->addSql('DROP INDEX UNIQ_AA38402584A0A3ED ON tweets');
        $this->addSql('ALTER TABLE tweets DROP content_id');
    }
}
