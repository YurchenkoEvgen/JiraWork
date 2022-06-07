<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607154525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE issue_field_value');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_field_value (uid INT AUTO_INCREMENT NOT NULL, value_project_id INT DEFAULT NULL, issue_id INT NOT NULL, issue_field_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, value VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_549B9B6B342A9B20 (value_project_id), INDEX IDX_549B9B6B5E7AA58C (issue_id), INDEX IDX_549B9B6B939F2D1B (issue_field_id), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B342A9B20 FOREIGN KEY (value_project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B939F2D1B FOREIGN KEY (issue_field_id) REFERENCES issue_field (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
