<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/18/2017
 * Time: 10:40 PM
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AbstractDataFixture;
use AppBundle\Entity\Seed;
use Doctrine\Common\Persistence\ObjectManager;

class SeedFixtures extends AbstractDataFixture
{
    public function load(ObjectManager $manager)
    {
        $csv = $this->container->get('kernel')->getRootDir() . '/data/seed_links.csv';
        $content = @file_get_contents($csv);
        $lines= str_getcsv($content, "\n");

        $links = [];

        foreach ($lines as $line) {
            $links[] = str_getcsv($line);
        }

        foreach ($links as $link) {
            list($url, $title, $isDone) = $link;
            $seed = new Seed($url, $title, (bool)$isDone);
            $manager->persist($seed);
        }

        $manager->flush();
    }
}