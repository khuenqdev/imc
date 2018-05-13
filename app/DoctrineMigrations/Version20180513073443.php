<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180513073443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Task (id INT AUTO_INCREMENT NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, finished TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Image CHANGE path path VARCHAR(255) DEFAULT NULL, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 6) DEFAULT NULL, CHANGE longitude longitude NUMERIC(10, 6) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE is_location_correct is_location_correct TINYINT(1) DEFAULT NULL, CHANGE domain domain VARCHAR(5) DEFAULT NULL, CHANGE thumbnail thumbnail VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Task');
        $this->addSql('ALTER TABLE Image CHANGE path path VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT \'NULL\', CHANGE latitude latitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE longitude longitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE is_location_correct is_location_correct TINYINT(1) DEFAULT \'NULL\', CHANGE domain domain VARCHAR(5) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE thumbnail thumbnail VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
