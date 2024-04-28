<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240428091420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE featuring (id INT AUTO_INCREMENT NOT NULL, id_song_id INT NOT NULL, id_featuring VARCHAR(90) NOT NULL, INDEX IDX_73A30F0C7E201B83 (id_song_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE featuring_artist (featuring_id INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_80914391A71CD2BA (featuring_id), INDEX IDX_80914391B7970CF8 (artist_id), PRIMARY KEY(featuring_id, artist_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE label (id INT AUTO_INCREMENT NOT NULL, id_label VARCHAR(90) NOT NULL, label_name VARCHAR(90) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE label_has_artist (id INT AUTO_INCREMENT NOT NULL, id_artist_id INT NOT NULL, id_label_id INT NOT NULL, sign_at DATETIME NOT NULL, left_at DATETIME DEFAULT NULL, INDEX IDX_FF9D48D937A2B0DF (id_artist_id), INDEX IDX_FF9D48D96362C3AC (id_label_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE featuring ADD CONSTRAINT FK_73A30F0C7E201B83 FOREIGN KEY (id_song_id) REFERENCES song (id)');
        $this->addSql('ALTER TABLE featuring_artist ADD CONSTRAINT FK_80914391A71CD2BA FOREIGN KEY (featuring_id) REFERENCES featuring (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE featuring_artist ADD CONSTRAINT FK_80914391B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE label_has_artist ADD CONSTRAINT FK_FF9D48D937A2B0DF FOREIGN KEY (id_artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE label_has_artist ADD CONSTRAINT FK_FF9D48D96362C3AC FOREIGN KEY (id_label_id) REFERENCES label (id)');
        $this->addSql('ALTER TABLE album ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD update_at DATETIME NOT NULL, CHANGE year year DATETIME NOT NULL, CHANGE nom name VARCHAR(90) NOT NULL');
        $this->addSql('ALTER TABLE artist ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD avatar LONGTEXT DEFAULT NULL, ADD followers LONGTEXT DEFAULT NULL, CHANGE label active VARCHAR(90) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user CHANGE date_birth date_birth DATETIME NOT NULL, CHANGE sexe sexe VARCHAR(55) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE featuring DROP FOREIGN KEY FK_73A30F0C7E201B83');
        $this->addSql('ALTER TABLE featuring_artist DROP FOREIGN KEY FK_80914391A71CD2BA');
        $this->addSql('ALTER TABLE featuring_artist DROP FOREIGN KEY FK_80914391B7970CF8');
        $this->addSql('ALTER TABLE label_has_artist DROP FOREIGN KEY FK_FF9D48D937A2B0DF');
        $this->addSql('ALTER TABLE label_has_artist DROP FOREIGN KEY FK_FF9D48D96362C3AC');
        $this->addSql('DROP TABLE featuring');
        $this->addSql('DROP TABLE featuring_artist');
        $this->addSql('DROP TABLE label');
        $this->addSql('DROP TABLE label_has_artist');
        $this->addSql('ALTER TABLE album DROP created_at, DROP update_at, CHANGE year year INT NOT NULL, CHANGE name nom VARCHAR(90) NOT NULL');
        $this->addSql('ALTER TABLE artist DROP created_at, DROP updated_at, DROP avatar, DROP followers, CHANGE active label VARCHAR(90) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE sexe sexe VARCHAR(10) DEFAULT NULL, CHANGE date_birth date_birth DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
