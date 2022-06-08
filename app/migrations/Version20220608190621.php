<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220608190621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_field_value (id INT AUTO_INCREMENT NOT NULL, issue_id INT NOT NULL, issue_filed_id VARCHAR(255) NOT NULL, value_project_id INT DEFAULT NULL, value_issue_id INT DEFAULT NULL, value_user_id INT DEFAULT NULL, datacolumn VARCHAR(50) NOT NULL, is_array TINYINT(1) NOT NULL, value_string VARCHAR(2048) DEFAULT NULL, value_float DOUBLE PRECISION DEFAULT NULL, value_date DATETIME DEFAULT NULL, INDEX IDX_549B9B6B5E7AA58C (issue_id), INDEX IDX_549B9B6BB9801EA2 (issue_filed_id), INDEX IDX_549B9B6B342A9B20 (value_project_id), INDEX IDX_549B9B6B132366A1 (value_issue_id), INDEX IDX_549B9B6B4CCCA6F5 (value_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, account_id VARCHAR(128) NOT NULL, email_address VARCHAR(255) DEFAULT NULL, display_name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6BB9801EA2 FOREIGN KEY (issue_filed_id) REFERENCES issue_field (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B342A9B20 FOREIGN KEY (value_project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B132366A1 FOREIGN KEY (value_issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B4CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE issue_field ADD is_array TINYINT(1) NOT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value DROP FOREIGN KEY FK_549B9B6B4CCCA6F5');
        $this->addSql('DROP TABLE issue_field_value');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE issue_field DROP is_array, CHANGE type type VARCHAR(255) NOT NULL');
    }
}
