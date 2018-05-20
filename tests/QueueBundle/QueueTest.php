<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:49 PM
 */

namespace Tests\QueueBundle;

use AppBundle\Entity\Link;
use QueueBundle\Queue;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QueueTest extends KernelTestCase
{
    /**
     * @var Queue
     */
    protected $service;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('queue');
    }

    /**
     * Test add link
     */
    public function testAddLink()
    {
        $link1 = new Link('http://example.com', '', 0.8);
        $link2 = new Link('http://example1.com', '', 0.5);
        $link3 = new Link('http://example2.com', '', 0.33);
        $link4 = new Link('http://example3.com', '', 0.81);
        $link5 = new Link('http://example4.com', '', 0.83);
        $link6 = new Link('http://example5.com', '', 1.0);
        $link7 = new Link('http://example6.com', '', 1.0);
        $this->service->addLink($link1);
        $this->service->addLink($link2);
        $this->service->addLink($link3);
        $this->service->addLink($link4);
        $this->service->addLink($link5);
        $this->service->addLink($link6);
        $this->service->addLink($link7);

        $this->assertEquals(7, $this->service->getSize());
    }

    /**
     * Test get next link
     */
    public function testGetNextLink()
    {
        $link1 = new Link('http://example.com', '', 0.8);
        $link2 = new Link('http://example1.com', '', 0.5);
        $link3 = new Link('http://example2.com', '', 0.33);
        $link4 = new Link('http://example3.com', '', 0.81);
        $link5 = new Link('http://example4.com', '', 0.83);
        $link6 = new Link('http://example5.com', '', 1.0);
        $link7 = new Link('http://example6.com', '', 1.0);
        $this->service->addLink($link1);
        $this->service->addLink($link2);
        $this->service->addLink($link3);
        $this->service->addLink($link4);
        $this->service->addLink($link5);
        $this->service->addLink($link6);
        $this->service->addLink($link7);

        $link = $this->service->getNextLink();
        $this->assertEquals('http://example5.com', $link->url);
    }
}