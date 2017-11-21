<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:51 PM
 */

namespace Tests\DownloaderBundle\Helpers;


use AppBundle\Entity\Link;
use AppBundle\Services\Helpers\Image;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageTest extends KernelTestCase
{
    /**
     * @var Image
     */
    private $service;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('helper.image');
    }

    public function testDownload()
    {
        $source = new Link('www.locationscout.net', 'Locationscout', 1.0, true);
        $src = 'https://www.locationscout.net/public/design/splash/iceland.jpg';
        $result = $this->service->download($source, $src);
        $this->assertTrue($result);
    }
}