<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623033911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BB1BC1B5A76ED395 ON stock_movement (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BB1BC1B5A76ED395 ON stock_movement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP user_id
        SQL);
    }
}
