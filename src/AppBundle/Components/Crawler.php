<?php

namespace AppBundle\Components;

use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Exception\Exception;

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

    /**
     * @var bool
     */
    protected $outputToCommandLine = false;

    /**
     * Crawler constructor.
     *
     * @param EntityManager $em
     * @param Queue $queue
     * @param Downloader $downloader
     */
    public function __construct(EntityManager $em, Queue $queue, Downloader $downloader)
    {
        $this->em = $em;
        $this->queue = $queue;
        $this->downloader = $downloader;
    }

    /**
     * Whether the crawler output relevant information to commandline interface
     *
     * @param $outputToCommandLine
     * @return $this
     */
    public function setOutputToCommandLine($outputToCommandLine)
    {
        $this->outputToCommandLine = $outputToCommandLine;

        return $this;
    }

    /**
     * Main crawling logic
     */
    public function crawl()
    {
        // Get next link in queue
        $link = $this->queue->getNextLink();

        // If the queue contains no link, stop the crawler
        if (!$link) {
            return false;
        }

        // Output link information
        if ($this->outputToCommandLine) {
            echo $link->getUrl() . "|" . $link->getRelevance() . "\n";
        }

        // Check if the page was already retrieved
        if ($this->em->getRepository(Page::class)->findOneBy(['url' => $link->getUrl()])) {
            return $this->crawl();
        }

        try {
            // Otherwise, create a new page object
            $page = new Page($link->getUrl(), $link->getTitle(), $link->getPage());

            // Download the page content
            $this->downloader->download($page);

            // Add its link to the queue
            $this->queue->addLinks($page->getLinks()->toArray());

            // Mark the current link as visited
            $link->setVisited(true);
            $this->em->persist($link);
            $this->em->flush($link);
        } catch (Exception $e) {
            // Output link information
            if ($this->outputToCommandLine) {
                echo $e->getMessage() . "\n";
            }
        }


        // Continue the crawling process
        return $this->crawl();
    }

}