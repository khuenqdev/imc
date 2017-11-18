<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:49 PM
 */

namespace Tests\QueueBundle;

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

    public function testAddLink()
    {
        $this->service->addLink('http://example.com', 'Example', 1.0);
    }

    public function testGetNextLink()
    {

    }
}