<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114084926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_categories (resource_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_5AEB01EE89329D25 (resource_id), INDEX IDX_5AEB01EE12469DE2 (category_id), PRIMARY KEY(resource_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resource_categories ADD CONSTRAINT FK_5AEB01EE89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_categories ADD CONSTRAINT FK_5AEB01EE12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C12469DE2');
        $this->addSql('ALTER TABLE resource_category DROP FOREIGN KEY FK_A8C0D36C89329D25');
        $this->addSql('DROP TABLE resource_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource_category (resource_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A8C0D36C89329D25 (resource_id), INDEX IDX_A8C0D36C12469DE2 (category_id), PRIMARY KEY(resource_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_category ADD CONSTRAINT FK_A8C0D36C89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_categories DROP FOREIGN KEY FK_5AEB01EE89329D25');
        $this->addSql('ALTER TABLE resource_categories DROP FOREIGN KEY FK_5AEB01EE12469DE2');
        $this->addSql('DROP TABLE resource_categories');
    }
}
