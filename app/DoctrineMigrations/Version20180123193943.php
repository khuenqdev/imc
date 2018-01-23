<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180123193943 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Image ADD is_location_correct TINYINT(1) DEFAULT NULL, CHANGE alt alt VARCHAR(255) DEFAULT NULL, CHANGE path path VARCHAR(255) DEFAULT NULL, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 6) DEFAULT NULL, CHANGE longitude longitude NUMERIC(10, 6) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE Image SET is_location_correct = 1 WHERE is_exif_location = 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Image DROP is_location_correct, CHANGE alt alt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE path path VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT \'NULL\', CHANGE latitude latitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE longitude longitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
