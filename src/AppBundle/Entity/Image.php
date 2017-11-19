<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Image
 */
class Image
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $src;

    /**
     * @var string
     */
    public $path;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $alt;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $copyright;

    /**
     * @var bool
     */
    public $isExifLocation;

    /**
     * @var \DateTime
     */
    public $dateTaken;


    /**
     * @var \DateTime
     */
    public $dateAcquired;

    /**
     * Link to page contains the image
     *
     * @var Link
     */
    public $source;

    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $zipcode;

    /**
     * @var string
     */
    public $country;

    public function __construct(Link $source, $src)
    {
        $this->src = $src;
        $this->source = $source;
    }
}
