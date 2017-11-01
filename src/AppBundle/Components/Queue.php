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

}