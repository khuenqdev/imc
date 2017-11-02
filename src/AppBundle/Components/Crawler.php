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
    }

    public function crawl()
    {
        $link = $this->queue->getNextLink();

        if (!$link) {
            return;
        }


    }

}