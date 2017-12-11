<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171211160936 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Image ADD description VARCHAR(512) DEFAULT NULL, CHANGE source source INT DEFAULT NULL, CHANGE alt alt VARCHAR(512) DEFAULT NULL, CHANGE path path VARCHAR(512) DEFAULT NULL, CHANGE author author VARCHAR(128) DEFAULT NULL, CHANGE copyright copyright VARCHAR(512) DEFAULT NULL, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT NULL, CHANGE date_taken date_taken DATETIME DEFAULT NULL, CHANGE date_acquired date_acquired DATETIME DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 6) DEFAULT NULL, CHANGE longitude longitude NUMERIC(10, 6) DEFAULT NULL, CHANGE altitude altitude NUMERIC(10, 6) DEFAULT NULL, CHANGE address address VARCHAR(512) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Image DROP description, CHANGE source source INT DEFAULT NULL, CHANGE alt alt VARCHAR(512) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE path path VARCHAR(512) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE author author VARCHAR(128) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE copyright copyright VARCHAR(512) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE is_exif_location is_exif_location TINYINT(1) DEFAULT \'NULL\', CHANGE date_taken date_taken DATETIME DEFAULT \'NULL\', CHANGE date_acquired date_acquired DATETIME DEFAULT \'NULL\', CHANGE latitude latitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE longitude longitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE altitude altitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE address address VARCHAR(512) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
