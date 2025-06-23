<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623032130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD product_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B54584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BB1BC1B54584665A ON stock_movement (product_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B54584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BB1BC1B54584665A ON stock_movement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_movement DROP product_id
        SQL);
    }
}
