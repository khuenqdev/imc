<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/19/2017
 * Time: 8:18 PM
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\DataFixtures\AbstractDataFixture;
use AppBundle\Entity\GeoName;
use Doctrine\Common\Persistence\ObjectManager;

class GeoNameFixtures extends AbstractDataFixture
{
    /**
     * Load fixture data
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $file1 = $this->container->get('kernel')->getRootDir() . '/data/cities/cities1000.txt';
        $file2 = $this->container->get('kernel')->getRootDir() . '/data/cities/cities5000.txt';
        $file3 = $this->container->get('kernel')->getRootDir() . '/data/cities/cities15000.txt';

        $this->importCities($manager, $file1);
        $this->importCities($manager, $file2);
        $this->importCities($manager, $file3);
    }

    /**
     * Import cities from text files
     *
     * @param ObjectManager $manager
     * @param $filename
     */
    private function importCities(ObjectManager $manager, $filename)
    {
        $content = @file_get_contents($filename);
        $lines = str_getcsv($content, "\n");

        $cities = [];

        foreach ($lines as $line) {
            $line = preg_replace("/\"/", "", $line);
            $cities[] = str_getcsv($line, "\t", "");
        }

        foreach ($cities as $city) {
            $geoName = GeoName::createFromTxtData($city);
            $manager->persist($geoName);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            SeedFixtures::class,
        ];
    }
}