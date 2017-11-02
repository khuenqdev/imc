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

    /**
     * Add a set of links to the queue
     *
     * @param array $links
     */
    public function addLinks(array $links)
    {
        foreach ($links as $link) {
            $this->addLink($link);
        }
    }

    /**
     * Add a link to the queue
     *
     * @param Link $link
     * @return $this
     */
    public function addLink(Link $link)
    {
        // Ignore visited link or link that is already in queue
        if ($link->isVisited() || $this->hasLink($link)) {
            return $this;
        }

        // 1. Place the link at the end of the heap
        $this->links[] = $link;
        $currentIdx = $this->getLength() - 1;

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

        return $this;
    }

    /**
     * Get first link in the heap array (no priority consideration)
     *
     * @return mixed|Link
     */
    public function getFirstLink()
    {
        return reset($this->links);
    }

    /**
     * Get last link in the heap array (no priority consideration)
     *
     * @return mixed|Link
     */
    public function getLastLink()
    {
        return end($this->links);
    }

    /**
     * Get next link out of the queue
     *
     * @return mixed|Link
     */
    public function getNextLink()
    {
        // The next link is the first link in queue
        $next = reset($this->links);

        /** Reorganize the queue */
        $this->links[0] = array_pop($this->links);
        $this->heapifyDown($this->links[0]);

        return $next;
    }

    /**
     * @param Link $link
     * @param int $currentIdx
     * @param int $leftIdx
     * @param int $rightIdx
     */
    private function heapifyDown(Link $link, $currentIdx = 0, $leftIdx = 1, $rightIdx = 2)
    {
        $left = isset($this->links[$leftIdx]) ? $this->links[$leftIdx] : null;
        $right = isset($this->links[$rightIdx]) ? $this->links[$rightIdx] : null;

        if ($left && $left->getRelevance() > $link->getRelevance()) {
            $this->links[$leftIdx] = $link;
            $this->links[$currentIdx] = $left;
            $currentIdx = $leftIdx;
            $this->heapifyDown($link, $currentIdx, $currentIdx * 2 + 1, $currentIdx * 2 + 2);
        }

        if ($right && $right->getRelevance() > $link->getRelevance()) {
            $this->links[$rightIdx] = $link;
            $this->links[$currentIdx] = $right;
            $currentIdx = $rightIdx;
            $this->heapifyDown($link, $currentIdx, $currentIdx * 2 + 1, $currentIdx * 2 + 2);
        }
    }

    /**
     * Get all links in the queue
     *
     * @return array
     */
    public function getAllLinks()
    {
        return $this->links;
    }

    /**
     * Get relevance tree which is a binary heap
     *
     * @return array
     */
    public function getRelevanceTree()
    {
        $tree = [];

        /** @var Link $link */
        foreach ($this->links as $idx => $link) {
            $parentIdx = (int)floor(($idx - 1) / 2);
            $leftIdx = 2 * $idx + 1;
            $rightIdx = 2 * $idx + 2;

            $tree[$idx] = [
                'current' => $link->getRelevance(),
                'parent' => isset($this->links[$parentIdx]) ? $this->links[$parentIdx]->getRelevance() : null,
                'left' => isset($this->links[$leftIdx]) ? $this->links[$leftIdx]->getRelevance() : null,
                'right' => isset($this->links[$rightIdx]) ? $this->links[$rightIdx]->getRelevance() : null,
            ];
        }

        return $tree;
    }

    /**
     * Get the total number of elements in the queue
     *
     * @return int
     */
    public function getLength()
    {
        return count($this->links);
    }

    /**
     * Check if the link is already inside the queue
     *
     * @param $link
     * @return bool
     */
    public function hasLink($link)
    {
        return in_array($link, $this->links, true);
    }

    /**
     * Clear all links
     */
    public function clearLinks()
    {
        $this->links = [];
    }

    /**
     * Check if the queue is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->links);
    }
}