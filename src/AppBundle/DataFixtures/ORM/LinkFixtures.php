<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 04-Nov-17
 * Time: 17:14
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Link;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LinkFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $seed = new Link('https://www.locationscout.net/', 'Locationscout - Discover the best places for photography', 1);
        $manager->persist($seed);
        $manager->flush();
    }

}