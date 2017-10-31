<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/29/2017
 * Time: 4:02 PM
 */

namespace AppBundle\Components;


use AppBundle\Entity\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

class Queue extends ArrayCollection implements Collection
{
    public function getNextLink()
    {
        $this->filter(function() {

        });
    }
}