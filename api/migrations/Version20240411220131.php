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
        $this->addSql('create table search_banner_1 (like search_banner including all)');
        $this->addSql('alter table search_banner_1 inherit search_banner');
        $this->addSql('create table search_banner_2 (like search_banner including all)');
        $this->addSql('alter table search_banner_2 inherit search_banner');
        $this->addSql('create table search_banner_3 (like search_banner including all)');
        $this->addSql('alter table search_banner_3 inherit search_banner');
        $this->addSql('create table search_banner_4 (like search_banner including all)');
        $this->addSql('alter table search_banner_4 inherit search_banner');
        $this->addSql('create table search_banner_5 (like search_banner including all)');
        $this->addSql('alter table search_banner_5 inherit search_banner');
        $this->addSql('create table search_banner_6 (like search_banner including all)');
        $this->addSql('alter table search_banner_6 inherit search_banner');
        $this->addSql('create table search_banner_7 (like search_banner including all)');
        $this->addSql('alter table search_banner_7 inherit search_banner');
        $this->addSql('create table search_banner_8 (like search_banner including all)');
        $this->addSql('alter table search_banner_8 inherit search_banner');
        $this->addSql('create table search_banner_9 (like search_banner including all)');
        $this->addSql('alter table search_banner_9 inherit search_banner');
        $this->addSql('create table search_banner_10 (like search_banner including all)');
        $this->addSql('alter table search_banner_10 inherit search_banner');
        $this->addSql('alter table search_banner_1 add constraint partition_check check (feature_id >= 1 and feature_id < 100)');
        $this->addSql('alter table search_banner_2 add constraint partition_check check (feature_id >= 100 and feature_id < 200)');
        $this->addSql('alter table search_banner_3 add constraint partition_check check (feature_id >= 200 and feature_id < 300)');
        $this->addSql('alter table search_banner_4 add constraint partition_check check (feature_id >= 300 and feature_id < 400)');
        $this->addSql('alter table search_banner_5 add constraint partition_check check (feature_id >= 400 and feature_id < 500)');
        $this->addSql('alter table search_banner_6 add constraint partition_check check (feature_id >= 500 and feature_id < 600)');
        $this->addSql('alter table search_banner_7 add constraint partition_check check (feature_id >= 600 and feature_id < 700)');
        $this->addSql('alter table search_banner_8 add constraint partition_check check (feature_id >= 700 and feature_id < 800)');
        $this->addSql('alter table search_banner_9 add constraint partition_check check (feature_id >= 800 and feature_id < 900)');
        $this->addSql('alter table search_banner_10 add constraint partition_check check (feature_id >= 900 and feature_id < 10000)');
        $this->addSql("create OR REPLACE function partition_for_search_banner() returns trigger as $$
            BEGIN
            IF ( NEW.feature_id >= 1 AND 
                NEW.feature_id < 100 ) THEN    
                    INSERT INTO search_banner_1 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 100 AND   
                NEW.feature_id < 200 ) THEN  
                    INSERT INTO search_banner_2 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 200 AND   
                NEW.feature_id < 300 ) THEN  
                    INSERT INTO search_banner_3 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 300 AND   
                NEW.feature_id < 400 ) THEN  
                    INSERT INTO search_banner_4 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 400 AND   
                NEW.feature_id < 500 ) THEN  
                    INSERT INTO search_banner_5 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 500 AND   
                NEW.feature_id < 600 ) THEN  
                    INSERT INTO search_banner_6 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 600 AND   
                NEW.feature_id < 700 ) THEN  
                    INSERT INTO search_banner_7 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 700 AND   
                NEW.feature_id < 800 ) THEN  
                    INSERT INTO search_banner_8 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 800 AND   
                NEW.feature_id < 900 ) THEN  
                    INSERT INTO search_banner_9 VALUES (NEW.*);
            ELSIF ( NEW.feature_id >= 900 AND   
                NEW.feature_id < 10000 ) THEN  
                    INSERT INTO search_banner_10 VALUES (NEW.*);
            ELSE
                RAISE EXCEPTION 'Out of range.   
                    Fix the partition_for_search_banner() function!';
            END IF;
            RETURN NULL;
            END;
            $$
            LANGUAGE plpgsql");
        $this->addSql('CREATE TRIGGER partition_search_banner    
            BEFORE INSERT ON search_banner
            FOR EACH ROW EXECUTE FUNCTION partition_for_search_banner()');
        $this->addSql('ALTER TABLE search_banner ADD CONSTRAINT FK_B67FD7F8684EC833 FOREIGN KEY (banner_id) REFERENCES "banners" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE "banners_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE search_banner_id_seq CASCADE');
        $this->addSql('ALTER TABLE search_banner DROP CONSTRAINT FK_B67FD7F8684EC833');
        $this->addSql('DROP TABLE "banners"');
        $this->addSql('DROP TABLE search_banner_1');
        $this->addSql('DROP TABLE search_banner_2');
        $this->addSql('DROP TABLE search_banner_3');
        $this->addSql('DROP TABLE search_banner_4');
        $this->addSql('DROP TABLE search_banner_5');
        $this->addSql('DROP TABLE search_banner_6');
        $this->addSql('DROP TABLE search_banner_7');
        $this->addSql('DROP TABLE search_banner_8');
        $this->addSql('DROP TABLE search_banner_9');
        $this->addSql('DROP TABLE search_banner_10');
        $this->addSql('DROP TABLE search_banner');
    }
}
