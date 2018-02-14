<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 2/13/2018
 * Time: 10:10 PM
 */

namespace Tests\DownloaderBundle\Helpers;

use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers\Keyword;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KeywordTest extends KernelTestCase
{
    /**
     * @var Keyword
     */
    private $service;

    /**
     * @var EntityManager
     */
    private $em;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('helper.keyword');
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testExtract()
    {
        $text = "japan gets its own ‘orphan black’ … with a few twists";

        dump($this->service->extract($text));
    }
}