<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411220131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE "banners_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE search_banner_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "banners" (id INT NOT NULL, content VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "banners".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "banners".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE search_banner (id INT NOT NULL, banner_id INT NOT NULL, tag_id INT NOT NULL, feature_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B67FD7F8684EC833 ON search_banner (banner_id)');
        $this->addSql('CREATE UNIQUE INDEX BANNER_IDX ON search_banner (tag_id, feature_id)');
        $this->addSql('ALTER TABLE search_banner ADD CONSTRAINT FK_B67FD7F8684EC833 FOREIGN KEY (banner_id) REFERENCES "banners" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE "banners_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE search_banner_id_seq CASCADE');
        $this->addSql('ALTER TABLE search_banner DROP CONSTRAINT FK_B67FD7F8684EC833');
        $this->addSql('DROP TABLE "banners"');
        $this->addSql('DROP TABLE search_banner');
    }
}
