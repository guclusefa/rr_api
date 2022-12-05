<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205141050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_user (resource_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_82B376F389329D25 (resource_id), INDEX IDX_82B376F3A76ED395 (user_id), PRIMARY KEY(resource_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resource_user ADD CONSTRAINT FK_82B376F389329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_user ADD CONSTRAINT FK_82B376F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource_user DROP FOREIGN KEY FK_82B376F389329D25');
        $this->addSql('ALTER TABLE resource_user DROP FOREIGN KEY FK_82B376F3A76ED395');
        $this->addSql('DROP TABLE resource_user');
    }
}
