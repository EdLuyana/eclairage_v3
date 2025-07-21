<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250628151542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD location_id INT NOT NULL, ADD size VARCHAR(10) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B564D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BB1BC1B564D218E ON stock_movement (location_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B564D218E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BB1BC1B564D218E ON stock_movement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP location_id, DROP size
        SQL);
    }
}
