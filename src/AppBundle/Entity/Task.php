<?php

namespace AppBundle\Entity;

/**
 * Task
 */
class Task
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var \DateTime
     */
    public $startAt;

    /**
     * @var \DateTime
     */
    public $endAt;

    /**
     * @var bool
     */
    public $finished;

    /**
     * Task constructor.
     *
     * @param $startAt
     * @param $endAt
     * @param bool $finished
     */
    public function __construct($startAt = null, $endAt = null, $finished = false)
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt;
        $this->finished = $finished;
    }
}

