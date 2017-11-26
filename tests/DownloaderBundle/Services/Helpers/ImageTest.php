<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:51 PM
 */

namespace Tests\DownloaderBundle\Helpers;


use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers\Image;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageTest extends KernelTestCase
{
    /**
     * @var Image
     */
    private $service;

    /**
     * @var EntityManager
     */
    private $em;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('helper.image');
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testDownload()
    {
        $source = $this->em->getRepository(Link::class)->findOneBy(['url' => 'https://www.locationscout.net/']);

        if (!$source) {
            $source = new Link('https://www.locationscout.net/', 'Locationscout - Discover the best places for photography', 1.0, false);
        }

        $src = 'https://farm5.staticflickr.com/4365/35988358033_5faa341d9e_o_d.jpg';
        $result = $this->service->download($source, $src);
        echo $this->service->getErrorMessage() . "\n";
        $this->assertTrue($result);
    }
}