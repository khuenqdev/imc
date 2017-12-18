<?php

namespace AppBundle\Entity;

/**
 * Seed
 */
class Seed
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $isDone;

    /**
     * Seed constructor.
     *
     * @param $title
     * @param $url
     * @param bool $isDone
     */
    public function __construct($url, $title, $isDone = false)
    {
        $this->title = $title;
        $this->url = $url;
        $this->isDone = $isDone;
    }
}

