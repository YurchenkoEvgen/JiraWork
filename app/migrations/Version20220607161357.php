<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607161357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value_issue DROP FOREIGN KEY FK_5D84C6F3D7ABF8B8');
        $this->addSql('ALTER TABLE issue_field_value_issue_field DROP FOREIGN KEY FK_22C14387D7ABF8B8');
        $this->addSql('DROP TABLE issue_field_value');
        $this->addSql('DROP TABLE issue_field_value_issue');
        $this->addSql('DROP TABLE issue_field_value_issue_field');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_field_value (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, value VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE issue_field_value_issue (issue_field_value_id INT NOT NULL, issue_id INT NOT NULL, INDEX IDX_5D84C6F35E7AA58C (issue_id), INDEX IDX_5D84C6F3D7ABF8B8 (issue_field_value_id), PRIMARY KEY(issue_field_value_id, issue_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE issue_field_value_issue_field (issue_field_value_id INT NOT NULL, issue_field_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_22C14387939F2D1B (issue_field_id), INDEX IDX_22C14387D7ABF8B8 (issue_field_value_id), PRIMARY KEY(issue_field_value_id, issue_field_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE issue_field_value_issue ADD CONSTRAINT FK_5D84C6F35E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE issue_field_value_issue ADD CONSTRAINT FK_5D84C6F3D7ABF8B8 FOREIGN KEY (issue_field_value_id) REFERENCES issue_field_value (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE issue_field_value_issue_field ADD CONSTRAINT FK_22C14387939F2D1B FOREIGN KEY (issue_field_id) REFERENCES issue_field (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE issue_field_value_issue_field ADD CONSTRAINT FK_22C14387D7ABF8B8 FOREIGN KEY (issue_field_value_id) REFERENCES issue_field_value (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
