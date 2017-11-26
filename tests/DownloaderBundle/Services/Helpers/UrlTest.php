<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/26/2017
 * Time: 8:44 PM
 */

namespace Tests\DownloaderBundle\Helpers;


use DownloaderBundle\Services\Helpers\Url;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UrlTest extends KernelTestCase
{
    /**
     * @var Url
     */
    protected $service;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('helper.url');
    }

    public function testParse()
    {
        $base = 'https://www.locationscout.net/';
        $url = 'https://www.locationscout.net/#slow-down';
        $parsed = $this->service->parse($url, $base);
        $this->assertSame($base, $parsed);
    }
}