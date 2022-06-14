<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614143002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value DROP FOREIGN KEY FK_549B9B6B4CCCA6F5');
        $this->addSql('ALTER TABLE issue_field_value CHANGE value_user_id value_user_id VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user ADD _self VARCHAR(250) NOT NULL, ADD account_type VARCHAR(50) NOT NULL, DROP id, CHANGE account_id account_id VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user ADD PRIMARY KEY (account_id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B4CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES user (account_id) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value DROP FOREIGN KEY FK_549B9B6B4CCCA6F5');
        $this->addSql('ALTER TABLE issue_field_value CHANGE value_user_id value_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD id INT AUTO_INCREMENT NOT NULL, DROP _self, DROP account_type, CHANGE account_id account_id VARCHAR(128) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B4CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
