<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123102454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_exploit (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_D6CD95DA89329D25 (resource_id), INDEX IDX_D6CD95DAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resource_exploit ADD CONSTRAINT FK_D6CD95DA89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE resource_exploit ADD CONSTRAINT FK_D6CD95DAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource_exploit DROP FOREIGN KEY FK_D6CD95DA89329D25');
        $this->addSql('ALTER TABLE resource_exploit DROP FOREIGN KEY FK_D6CD95DAA76ED395');
        $this->addSql('DROP TABLE resource_exploit');
    }
}
