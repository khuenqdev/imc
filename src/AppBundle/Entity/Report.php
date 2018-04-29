<?php

namespace AppBundle\Entity;

/**
 * Report
 */
class Report
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
     * @var double
     */
    public $executionTime;

    /**
     * @var double
     */
    public $memoryUsage;

    /**
     * @var int
     */
    public $noOfLinks;

    /**
     * @var int
     */
    public $noOfVisitedLinks;

    /**
     * @var int
     */
    public $noOfImages;

    /**
     * @var int
     */
    public $noOfExifImages;

    /**
     * Report constructor.
     */
    public function __construct() {
        $this->startAt = new \DateTime();
        $this->noOfLinks = 0;
        $this->noOfVisitedLinks = 0;
        $this->noOfImages = 0;
        $this->noOfExifImages = 0;
        $this->executionTime = 0;
    }
}

