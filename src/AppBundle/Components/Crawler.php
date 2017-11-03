<?php

namespace AppBundle\Components;

use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:43
 */
class Crawler
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Downloader
     */
    protected $downloader;

    public function __construct(EntityManager $em, Queue $queue, Downloader $downloader)
    {
        $this->em = $em;
        $this->queue = $queue;
    }

    public function crawl()
    {
        // Get next link in queue
        $link = $this->queue->getNextLink();

        // If the queue contains no link, stop the crawler
        if (!$link) {
            return;
        }

        // Create a new page object
        $page = new Page($link->getUrl(), $link->getTitle());

        // Download the page content
        $this->downloader->download($page);

        // Add its link to the queue
        $this->queue->addLinks($page->getLinks()->toArray());
    }

}