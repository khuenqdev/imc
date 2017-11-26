<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 12:57 AM
 */

namespace Tests\Downloader;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Downloader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DownloaderTest extends KernelTestCase
{
    /**
     * @var Downloader
     */
    protected $service;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Set up the test
     */
    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('downloader');
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Test download method
     */
    public function testDownload()
    {
        $link = $this->em->getRepository(Link::class)->findOneBy(['url' => 'https://www.locationscout.net/']);

        if (!$link) {
            $link = new Link('https://www.locationscout.net/', 'Locationscout - Discover the best places for photography', 1.0, false);
        }

        $this->service->download($link);
        echo $this->service->getErrorMessage() . "\n";
    }
}