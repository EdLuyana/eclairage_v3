<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250721180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insertion des tailles standardisées dans la table size';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO size (value) VALUES
            ('XS'), ('S'), ('M'), ('L'), ('XL'), ('XXL'),
            ('32'), ('34'), ('36'), ('38'), ('40'), ('42'), ('44'), ('46'), ('48'), ('50'), ('52'),
            ('U')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM size WHERE value IN (
            'XS', 'S', 'M', 'L', 'XL', 'XXL',
            '32', '34', '36', '38', '40', '42', '44', '46', '48', '50', '52',
            'U'
        )");
    }
}
