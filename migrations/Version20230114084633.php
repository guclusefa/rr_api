<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114084633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, relation_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, media VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, visibility SMALLINT NOT NULL, is_published TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, is_suspended TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_BC91F416F675F31B (author_id), INDEX IDX_BC91F4163256915B (relation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_category (resource_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A8C0D36C89329D25 (resource_id), INDEX IDX_A8C0D36C12469DE2 (category_id), PRIMARY KEY(resource_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F4163256915B FOREIGN KEY (relation_id) REFERENCES relation (id)');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416F675F31B');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F4163256915B');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C89329D25');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE relation');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP TABLE resource_category');
    }
}
