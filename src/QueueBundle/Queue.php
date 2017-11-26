<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:47 PM
 */

namespace QueueBundle;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;

class Queue
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $links = [];

    /**
     * Hashes of links
     *
     * @var array
     */
    protected $hash = [];

    /**
     * Queue constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Add link to queue
     *
     * @param Link $link
     * @return $this
     */
    public function addLink(Link $link)
    {
        // 0. If the link already exist in queue or it has been visited or it is irrelevant then ignore
        if ($this->hasLink($link)) {
            return $this;
        }

        // 1. Place the link at the end of the heap
        $this->links[] = $link;
        $this->hash[] = hash('sha256', $link->url);
        $currentIdx = $this->getSize() - 1;

        // 2. Get temporary parent link in the heap
        $parentIdx = (int)floor(($currentIdx - 1) / 2);

        /** @var Link $parent */
        $parent = isset($this->links[$parentIdx]) ? $this->links[$parentIdx] : null;

        // 3. Swap the link with its temporary parent in the heap whenever the parent has smaller relevance score
        while ($parent && ($parent->relevance < $link->relevance)) {
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
     * Get next link out of the queue
     *
     * @return null|Link
     */
    public function getNextLink()
    {
        // Remove the first link from the queue (root node)
        $next = $this->getFirstLink();

        // If no link is found, return null
        if (!$next) {
            return null;
        }

        /** Reorganize the queue */
        if (!$this->isEmpty()) {
            $this->links[0] = array_pop($this->links);
            $this->maxHeapify(0);
        }

        return $next;
    }

    /**
     * Get first link
     *
     * @return mixed
     */
    public function getFirstLink()
    {
        return reset($this->links);
    }

    /**
     * Get last link
     *
     * @return mixed
     */
    public function getLastLink()
    {
        return end($this->links);
    }

    /**
     * Max heapify
     *
     * @param $i
     */
    private function maxHeapify($i)
    {
        $leftIdx = 2 * $i + 1;
        $rightIdx = 2 * $i + 2;
        $largestIdx = $i;

        $left = isset($this->links[$leftIdx]) ? $this->links[$leftIdx] : null;
        $right = isset($this->links[$rightIdx]) ? $this->links[$rightIdx] : null;
        $largest = isset($this->links[$largestIdx]) ? $this->links[$largestIdx] : null;

        if ($leftIdx < $this->getSize() && $left && $left->relevance > $largest->relevance) {
            $largest = $left;
            $largestIdx = $leftIdx;
        }

        if ($rightIdx < $this->getSize() && $right && $right->relevance > $largest->relevance) {
            $largest = $right;
            $largestIdx = $rightIdx;
        }

        if ($largestIdx != $i) {
            $this->links[$largestIdx] = $this->links[$i];
            $this->links[$i] = $largest;
            $this->maxHeapify($largestIdx);
        }
    }

    /**
     * Whether the queue and the database already contain the link
     *
     * @param Link $link
     * @return bool
     */
    public function hasLink(Link $link)
    {
        return in_array(hash('sha256', $link->url), $this->hash, true);
    }

    /**
     * Get queue size
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->links);
    }

    /**
     * Remove a link
     *
     * @param Link $link
     * @return $this
     */
    public function removeLink(Link $link)
    {
        if ($idx = array_search($link, $this->links, true)) {
            unset($this->links[$idx]);
        }

        return $this;
    }

    /**
     * Clear the queue
     */
    public function clear()
    {
        $this->links = [];

        return $this;
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

    /**
     * Get links
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Print queue links
     *
     * @return string
     */
    public function __toString()
    {
        $string = "";

        foreach ($this->links as $link) {
            $string .= "{$link->url}|{$link->relevance}\n";
        }

        return $string;
    }
}