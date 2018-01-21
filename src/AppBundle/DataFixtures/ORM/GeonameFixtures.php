<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/19/2017
 * Time: 8:18 PM
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\DataFixtures\AbstractDataFixture;
use AppBundle\Entity\Geoname;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\ResultSetMapping;

class GeonameFixtures extends AbstractDataFixture
{
    /**
     * Load fixture data
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
//        // Get geoname file path
//        $filePath = $this->container->get('kernel')->getRootDir() . '/data/geonames.txt';
//
//        // Run SQL for importing data to the database
//        $em = $this->container->get('doctrine.orm.entity_manager');
//        $rsm = new ResultSetMapping();
//        $query = $em->createNativeQuery("
//SELECT '########## Loading allCountries.txt... ##########';
//LOAD DATA LOCAL INFILE '{$filePath}'
//INTO TABLE Geoname
//CHARACTER SET 'utf8mb4'
//(id, name, ascii_name, alternate_names, latitude, longitude, feature_class, feature_code, country_code, cc2, admin1_code, admin2_code, admin3_code, admin4_code, population, elevation, dem, timezone, modification_date);
//        ", $rsm);
//        $query->execute();
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            StopwordFixtures::class
        ];
    }
}