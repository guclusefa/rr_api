<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230127110514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, author_id INT NOT NULL, reply_to_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_9474526C89329D25 (resource_id), INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526CFFDF7169 (reply_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, relation_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, media VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, visibility SMALLINT NOT NULL, is_published TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, is_suspended TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_BC91F416F675F31B (author_id), INDEX IDX_BC91F4163256915B (relation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_category (resource_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A8C0D36C89329D25 (resource_id), INDEX IDX_A8C0D36C12469DE2 (category_id), PRIMARY KEY(resource_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_consult (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_10A22E0A89329D25 (resource_id), INDEX IDX_10A22E0AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_exploit (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_D6CD95DA89329D25 (resource_id), INDEX IDX_D6CD95DAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_like (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_A343E00989329D25 (resource_id), INDEX IDX_A343E009A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_save (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_5A469A6489329D25 (resource_id), INDEX IDX_5A469A64A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_share (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_C4BDE76889329D25 (resource_id), INDEX IDX_C4BDE768A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_shared_to (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_FBE3D96B89329D25 (resource_id), INDEX IDX_FBE3D96BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_stats (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, nb_consults INT NOT NULL, nb_exploits INT NOT NULL, nb_likes INT NOT NULL, nb_saves INT NOT NULL, nb_shares INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_7CFC1D9889329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE state (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, state_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(20) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', first_name VARCHAR(20) DEFAULT NULL, last_name VARCHAR(20) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, birth_date DATE DEFAULT NULL, bio VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, is_certified TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, is_banned TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6495D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F4163256915B FOREIGN KEY (relation_id) REFERENCES relation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_consult ADD CONSTRAINT FK_10A22E0A89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_consult ADD CONSTRAINT FK_10A22E0AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_exploit ADD CONSTRAINT FK_D6CD95DA89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_exploit ADD CONSTRAINT FK_D6CD95DAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_like ADD CONSTRAINT FK_A343E00989329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_like ADD CONSTRAINT FK_A343E009A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_save ADD CONSTRAINT FK_5A469A6489329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_save ADD CONSTRAINT FK_5A469A64A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_share ADD CONSTRAINT FK_C4BDE76889329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_share ADD CONSTRAINT FK_C4BDE768A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_shared_to ADD CONSTRAINT FK_FBE3D96B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_shared_to ADD CONSTRAINT FK_FBE3D96BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_stats ADD CONSTRAINT FK_7CFC1D9889329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C89329D25');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CFFDF7169');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416F675F31B');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F4163256915B');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C89329D25');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C12469DE2');
        $this->addSql('ALTER TABLE resource_consult DROP FOREIGN KEY FK_10A22E0A89329D25');
        $this->addSql('ALTER TABLE resource_consult DROP FOREIGN KEY FK_10A22E0AA76ED395');
        $this->addSql('ALTER TABLE resource_exploit DROP FOREIGN KEY FK_D6CD95DA89329D25');
        $this->addSql('ALTER TABLE resource_exploit DROP FOREIGN KEY FK_D6CD95DAA76ED395');
        $this->addSql('ALTER TABLE resource_like DROP FOREIGN KEY FK_A343E00989329D25');
        $this->addSql('ALTER TABLE resource_like DROP FOREIGN KEY FK_A343E009A76ED395');
        $this->addSql('ALTER TABLE resource_save DROP FOREIGN KEY FK_5A469A6489329D25');
        $this->addSql('ALTER TABLE resource_save DROP FOREIGN KEY FK_5A469A64A76ED395');
        $this->addSql('ALTER TABLE resource_share DROP FOREIGN KEY FK_C4BDE76889329D25');
        $this->addSql('ALTER TABLE resource_share DROP FOREIGN KEY FK_C4BDE768A76ED395');
        $this->addSql('ALTER TABLE resource_shared_to DROP FOREIGN KEY FK_FBE3D96B89329D25');
        $this->addSql('ALTER TABLE resource_shared_to DROP FOREIGN KEY FK_FBE3D96BA76ED395');
        $this->addSql('ALTER TABLE resource_stats DROP FOREIGN KEY FK_7CFC1D9889329D25');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE relation');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP TABLE resource_category');
        $this->addSql('DROP TABLE resource_consult');
        $this->addSql('DROP TABLE resource_exploit');
        $this->addSql('DROP TABLE resource_like');
        $this->addSql('DROP TABLE resource_save');
        $this->addSql('DROP TABLE resource_share');
        $this->addSql('DROP TABLE resource_shared_to');
        $this->addSql('DROP TABLE resource_stats');
        $this->addSql('DROP TABLE state');
        $this->addSql('DROP TABLE user');
    }
}
