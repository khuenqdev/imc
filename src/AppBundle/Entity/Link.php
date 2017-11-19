<?php

namespace AppBundle\Entity;

/**
 * Link
 */
class Link
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
    public $visited;

    /**
     * @var float
     */
    public $relevance;

    /**
     * Link constructor.
     *
     * @param $url
     * @param $title
     * @param float $relevance
     * @param bool $visited
     */
    public function __construct($url, $title, $relevance = 1.0, $visited = false)
    {
        $this->url = $url;
        $this->title = $title;
        $this->relevance = $relevance;
        $this->visited = $visited;
    }
}

