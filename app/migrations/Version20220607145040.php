<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607145040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_field_value (uid INT AUTO_INCREMENT NOT NULL, issue_id INT DEFAULT NULL, issue_field_id VARCHAR(255) DEFAULT NULL, type VARCHAR(50) NOT NULL, value VARCHAR(2048) DEFAULT NULL, value_text LONGTEXT DEFAULT NULL, value_project VARCHAR(255) DEFAULT NULL, INDEX IDX_549B9B6B5E7AA58C (issue_id), INDEX IDX_549B9B6B939F2D1B (issue_field_id), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B939F2D1B FOREIGN KEY (issue_field_id) REFERENCES issue_field (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE issue_field_value');
    }
}
