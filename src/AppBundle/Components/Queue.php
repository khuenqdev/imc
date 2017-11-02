<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/29/2017
 * Time: 4:02 PM
 */

namespace AppBundle\Components;

use AppBundle\Entity\Link;

class Queue
{
    /**
     * A binary heap of links
     *
     * @var array
     */
    private $links = [];

    private $length = 0;

    /**
     * Add a link to the queue
     *
     * @param Link $link
     * @return $this
     */
    public function addLink(Link $link)
    {
        // 1. Place the link at the end of the heap
        $this->links[] = $link;
        $currentIdx = $this->length;

        // 2. Get temporary parent link in the heap
        $parentIdx = (int)floor(($currentIdx - 1) / 2);

        /** @var Link $parent */
        $parent = isset($this->links[$parentIdx]) ? $this->links[$parentIdx] : null;

        // 3. Swap the link with its temporary parent in the heap whenever the parent has smaller relevance score
        while ($parent && ($parent->getRelevance() < $link->getRelevance())) {
            $this->links[$parentIdx] = $link;
            $this->links[$currentIdx] = $parent;
            $currentIdx = $parentIdx;

            // Update temporary parent index and entity
            $parentIdx = (int)floor(($parentIdx - 1) / 2);
            $parent = isset($this->links[$parentIdx]) ? $this->links[$parentIdx] : null;
        }

        $this->length++;

        return $this;
    }

    /**
     * Get first link in the heap array (no priority consideration)
     *
     * @return mixed
     */
    public function getFirstLink()
    {
        return reset($this->links);
    }

    /**
     * Get last link in the heap array (no priority consideration)
     *
     * @return mixed
     */
    public function getLastLink()
    {
        return end($this->links);
    }

    public function getNextLink()
    {

    }

    /**
     * Get all links in the queue
     *
     * @param bool $asHeap
     * @return array
     */
    public function getAllLinks($asHeap = false)
    {
        if ($asHeap) {
            // @todo build a link heap for better presentation
            $heap = $this->buildLinkHeap();

            return $heap;
        }

        return $this->links;
    }

    /**
     * Get the total number of elements in the queue
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Build a link heap
     */
    private function buildLinkHeap()
    {
        return [];
    }

}