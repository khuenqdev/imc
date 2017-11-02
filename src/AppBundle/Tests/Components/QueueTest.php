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

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue
     */
    private $queue;

    public function setUp()
    {
        $this->queue = new Queue();
        $this->createTestLinks();

    }

    /**
     * Test adding links
     */
    public function testAddLink()
    {
        $link = new Link();
        $link->setRelevance(0.3);
        $this->queue->addLink($link);
        $this->assertSame(0.5, $this->queue->getFirstLink()->getRelevance());
        $this->assertSame(6, $this->queue->getLength());

        $linkMax = new Link();
        $linkMax->setRelevance(0.9);
        $this->queue->addLink($linkMax);
        $this->assertSame(0.9, $this->queue->getFirstLink()->getRelevance());
        $this->assertSame(7, $this->queue->getLength());
    }

    /**
     * Test get relevance tree
     */
    public function testGetRelevanceTree()
    {
        $heap = $this->queue->getRelevanceTree();

        $first = reset($heap);
        $last = end($heap);

        $this->assertSame(0.5, $first['current']);
        $this->assertSame(0.4, $first['left']);
        $this->assertSame(0.2, $first['right']);
        $this->assertSame(0.3, $last['current']);
        $this->assertSame(0.4, $last['parent']);
    }

    /**
     * Test get next link from the queue
     */
    public function testGetNextLink()
    {
        $expected = [0.4, 0.3, 0.2, 0.1];
        $len = $this->queue->getLength();

        foreach($expected as $ex) {
            $this->queue->getNextLink();
            $this->assertSame($ex, $this->queue->getFirstLink()->getRelevance());
            $this->assertSame(--$len, $this->queue->getLength());
        }
    }

    /**
     * Create test links
     */
    private function createTestLinks()
    {
        $rel = 0.0;
        for ($i = 0; $i < 5; $i++) {
            $l = new Link();
            $rel += 0.1;
            $l->setRelevance($rel);
            $this->queue->addLink($l);
        }
    }
}