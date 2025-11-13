<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113002723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bookings (id SERIAL NOT NULL, guest_id INT NOT NULL, house_id INT NOT NULL, comment TEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7A853C359A4AA658 ON bookings (guest_id)');
        $this->addSql('CREATE INDEX IDX_7A853C356BB74515 ON bookings (house_id)');
        $this->addSql('CREATE TABLE houses (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, price_per_night INT NOT NULL, capacity INT NOT NULL, distance_to_sea INT NOT NULL, amenities VARCHAR(255) NOT NULL, is_available BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE bookings ADD CONSTRAINT FK_7A853C359A4AA658 FOREIGN KEY (guest_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookings ADD CONSTRAINT FK_7A853C356BB74515 FOREIGN KEY (house_id) REFERENCES houses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C359A4AA658');
        $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C356BB74515');
        $this->addSql('DROP TABLE bookings');
        $this->addSql('DROP TABLE houses');
        $this->addSql('DROP TABLE users');
    }
}
