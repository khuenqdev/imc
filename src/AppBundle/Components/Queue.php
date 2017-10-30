<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/29/2017
 * Time: 4:02 PM
 */

namespace AppBundle\Components;


use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;

class Queue
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get the next link in queue, which is actually the most relevant one
     *
     * @return bool|Link
     */
    public function getNextLink()
    {
        $res = $this->em->getRepository(Link::class)->getMostRelevantLink();

        if (empty($res)) {
            return false;
        }

        return array_shift($res);
    }
}