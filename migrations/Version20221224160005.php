<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221224160005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(20) NOT NULL, first_name VARCHAR(20) DEFAULT NULL, last_name VARCHAR(20) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, birth_date DATE DEFAULT NULL, bio VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, is_banned TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
    }
}
