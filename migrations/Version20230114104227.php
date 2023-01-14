<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114104227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource_shared_visibilities DROP FOREIGN KEY FK_E5167D67A76ED395');
        $this->addSql('ALTER TABLE resource_shared_visibilities DROP FOREIGN KEY FK_E5167D6789329D25');
        $this->addSql('DROP TABLE resource_shared_visibilities');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_shared_visibilities (resource_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E5167D6789329D25 (resource_id), INDEX IDX_E5167D67A76ED395 (user_id), PRIMARY KEY(resource_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE resource_shared_visibilities ADD CONSTRAINT FK_E5167D67A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_shared_visibilities ADD CONSTRAINT FK_E5167D6789329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
    }
}
