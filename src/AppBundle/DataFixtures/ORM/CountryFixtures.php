<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/21/2017
 * Time: 11:46 AM
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AbstractDataFixture;
use AppBundle\Entity\Country;
use Doctrine\Common\Persistence\ObjectManager;

class CountryFixtures extends AbstractDataFixture
{

    /**
     * Load fixture data
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $file = $this->container->get('kernel')->getRootDir() . '/data/countries.txt';
        $this->importCountries($manager, $file);
    }

    /**
     * Import cities from text files
     *
     * @param ObjectManager $manager
     * @param $filename
     */
    private function importCountries(ObjectManager $manager, $filename)
    {
        $content = @file_get_contents($filename);
        $lines = str_getcsv($content, "\n");

        $countries = [];

        foreach ($lines as $line) {
            $line = preg_replace("/\"/", "", $line);
            $countries[] = str_getcsv($line, "\t", "");
        }

        foreach ($countries as $country) {
            $geoName = Country::createFromTxtData($country);
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
            SeedFixtures::class
        ];
    }
}