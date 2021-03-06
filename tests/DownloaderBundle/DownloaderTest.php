<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 12:57 AM
 */

namespace Tests\Downloader;

use AppBundle\Entity\Link;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Downloader;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

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
     * Test download a web page
     * @throws \Exception
     */
    public function testDownload()
    {
        $this->service->download('http://info.cern.ch/');
        $report = $this->service->getReport();

        $this->assertEquals(4, $report->noOfLinks);
        $this->assertEquals(0, $report->noOfImages);
    }
}