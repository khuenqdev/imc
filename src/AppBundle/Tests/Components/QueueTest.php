<?php

namespace AppBundle\Tests\Components;

use AppBundle\Components\Queue;
use AppBundle\Entity\Link;

/**
 * Created by PhpStorm.
 * User: khuenq
 * Date: 1.11.2017
 * Time: 15:39
 */

class QueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Queue
     */
    private $service;

    public function setUp()
    {
        $this->service = new Queue();
    }

    public function testAddLink()
    {
        $link1 = new Link();
        $link1->setRelevance(0.7);

        $link2 = new Link();
        $link2->setRelevance(0.1);

        $link3 = new Link();
        $link3->setRelevance(0.9);

        $link4 = new Link();
        $link4->setRelevance(0.3);

        $this->service->addLink($link1)
            ->addLink($link2)
            ->addLink($link3)
            ->addLink($link4);

        $link = $this->service->getFirstLink();

        $this->assertSame($link3, $link);
    }
}