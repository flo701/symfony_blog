<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230213143447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_bulletin (tag_id INT NOT NULL, bulletin_id INT NOT NULL, INDEX IDX_1D4CF299BAD26311 (tag_id), INDEX IDX_1D4CF299D1AAB236 (bulletin_id), PRIMARY KEY(tag_id, bulletin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_bulletin ADD CONSTRAINT FK_1D4CF299BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_bulletin ADD CONSTRAINT FK_1D4CF299D1AAB236 FOREIGN KEY (bulletin_id) REFERENCES bulletin (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_bulletin DROP FOREIGN KEY FK_1D4CF299BAD26311');
        $this->addSql('ALTER TABLE tag_bulletin DROP FOREIGN KEY FK_1D4CF299D1AAB236');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_bulletin');
    }
}
