<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 12:57 AM
 */

namespace Tests\Downloader;

use DownloaderBundle\Downloader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DownloaderTest extends KernelTestCase
{
    /**
     * @var Downloader
     */
    protected $service;

    /**
     * Set up the test
     */
    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('downloader');
    }

    /**
     * Test download method
     */
    public function testDownload()
    {
        $this->service->download('https://www.locationscout.net/');
        $pageCache = $this->service->getPage();
        dump($pageCache->links);
    }
}