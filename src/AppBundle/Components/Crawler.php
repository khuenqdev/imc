<?php

namespace AppBundle\Components;

use AppBundle\Entity\Page;
use AppBundle\Entity\Seed;
use Doctrine\ORM\EntityManager;

/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:43
 */
class Crawler
{
    protected $em;
    protected $queue;

    public function __construct(EntityManager $em, Queue $queue)
    {
        $this->em = $em;
        $this->queue = $queue;
        $this->initializeQueue();
    }

    public function crawl()
    {
        $link = $this->queue->getNextLink();

        if (!$link) {
            return;
        }


    }

    /**
     * Initialize the priority queue
     */
    protected function initializeQueue()
    {
        if (!$this->queue->isEmpty()) {
            return;
        }

        // Find an unfinished seed
        /** @var Seed $seed */
        $seed = $this->em->getRepository(Seed::class)->findOneBy(array('finished' => false));

        // Retrieve content of the page associated to the seed link
        $page = new Page();
        $html = $page->setUrl($seed->getUrl())
            ->setUrlTitle($seed->getTitle())
            ->setSeedId($seed->getId())
            ->setHost($seed->getHost())
            ->getHtml();

        // Extract all links on the page and put them into the priority queue
        $links = $page->extractLinks();
        $this->queue->addLinks($links);
    }

}