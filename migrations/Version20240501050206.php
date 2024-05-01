<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240501050206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE label_has_artist ADD id_artist_id INT NOT NULL, ADD id_label_id INT NOT NULL, DROP id_artist, DROP id_label');
        $this->addSql('ALTER TABLE label_has_artist ADD CONSTRAINT FK_FF9D48D937A2B0DF FOREIGN KEY (id_artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE label_has_artist ADD CONSTRAINT FK_FF9D48D96362C3AC FOREIGN KEY (id_label_id) REFERENCES label (id)');
        $this->addSql('CREATE INDEX IDX_FF9D48D937A2B0DF ON label_has_artist (id_artist_id)');
        $this->addSql('CREATE INDEX IDX_FF9D48D96362C3AC ON label_has_artist (id_label_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE label_has_artist DROP FOREIGN KEY FK_FF9D48D937A2B0DF');
        $this->addSql('ALTER TABLE label_has_artist DROP FOREIGN KEY FK_FF9D48D96362C3AC');
        $this->addSql('DROP INDEX IDX_FF9D48D937A2B0DF ON label_has_artist');
        $this->addSql('DROP INDEX IDX_FF9D48D96362C3AC ON label_has_artist');
        $this->addSql('ALTER TABLE label_has_artist ADD id_artist INT NOT NULL, ADD id_label INT NOT NULL, DROP id_artist_id, DROP id_label_id');
    }
}
