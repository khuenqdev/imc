<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:47 PM
 */

namespace QueueBundle;

use Doctrine\ORM\EntityManager;
use QueueBundle\Entity\Link;

class Queue
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Add link to queue
     *
     * @param $url
     * @param $title
     * @param $relevance
     */
    public function addLink($url, $title, $relevance)
    {
        if(!$this->em->getRepository(Link::class)->findOneBy(['hash' => sha1($url)])) {
            $link = new Link($url, $title, $relevance);
            $this->em->persist($link);
            $this->em->flush();
        }
    }

    /**
     * Get next link in queue
     *
     * @return bool|Link
     */
    public function getNextLink()
    {
        $repo = $this->em->getRepository(Link::class);
        $link = $repo->findMostRelevanceLink();

        if ($link) {
            // Delete the link from the queue before returning it
            $this->em->remove($link);
            $this->em->flush();
        }

        return $link;
    }

    /**
     * Clear the queue
     */
    public function clear()
    {
        $connection = $this->em->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('link', true));
    }
}