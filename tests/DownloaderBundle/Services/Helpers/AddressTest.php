<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 1/18/2018
 * Time: 4:29 PM
 */

namespace Tests\DownloaderBundle\Helpers;


use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressTest extends KernelTestCase
{
    /**
     * @var Address
     */
    private $service;

    /**
     * @var EntityManager
     */
    private $em;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('helper.address');
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testGetCity()
    {
        $text = 'The historical castles of Germany';
        dump($this->service->convertToAddressString($text));
    }
}