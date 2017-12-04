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

    public function testInsertLink()
    {
        $link = new Link('example.com', 'Example', 1.0, false);

        echo "Before insert: " . $link->id . "\n";

        $this->em->persist($link);
        $this->em->flush($link);

        echo "After insert: " . $link->id . "\n";
    }
}