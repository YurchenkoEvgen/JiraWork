<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607145436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value ADD issue_id INT NOT NULL, ADD issue_field_id VARCHAR(255) NOT NULL, DROP issue, DROP issue_field');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE issue_field_value ADD CONSTRAINT FK_549B9B6B939F2D1B FOREIGN KEY (issue_field_id) REFERENCES issue_field (id)');
        $this->addSql('CREATE INDEX IDX_549B9B6B5E7AA58C ON issue_field_value (issue_id)');
        $this->addSql('CREATE INDEX IDX_549B9B6B939F2D1B ON issue_field_value (issue_field_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_field_value DROP FOREIGN KEY FK_549B9B6B5E7AA58C');
        $this->addSql('ALTER TABLE issue_field_value DROP FOREIGN KEY FK_549B9B6B939F2D1B');
        $this->addSql('DROP INDEX IDX_549B9B6B5E7AA58C ON issue_field_value');
        $this->addSql('DROP INDEX IDX_549B9B6B939F2D1B ON issue_field_value');
        $this->addSql('ALTER TABLE issue_field_value ADD issue_field VARCHAR(255) NOT NULL, DROP issue_id, CHANGE issue_field_id issue VARCHAR(255) NOT NULL');
    }
}
