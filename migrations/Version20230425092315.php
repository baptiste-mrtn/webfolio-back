<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230425092315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C14E7AF8F');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1F6BD1646');
        $this->addSql('DROP INDEX IDX_64C19C1F6BD1646 ON category');
        $this->addSql('DROP INDEX IDX_64C19C14E7AF8F ON category');
        $this->addSql('ALTER TABLE category DROP gallery_id, DROP site_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD gallery_id INT DEFAULT NULL, ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C14E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1F6BD1646 ON category (site_id)');
        $this->addSql('CREATE INDEX IDX_64C19C14E7AF8F ON category (gallery_id)');
    }
}
