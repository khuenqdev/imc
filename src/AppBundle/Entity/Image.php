<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Image
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Image
{
    /**
     * @var int
     * @Serializer\Expose
     */
    public $id;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $filename;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $src;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $path;

    /**
     * @var int
     * @Serializer\Expose
     */
    public $width;

    /**
     * @var int
     * @Serializer\Expose
     */
    public $height;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     * @Serializer\Expose
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
     * @Serializer\Expose
     */
    public $latitude;

    /**
     * @var float
     * @Serializer\Expose
     */
    public $longitude;

    /**
     * @var float
     * @Serializer\Expose
     */
    public $altitude;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $address;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $description;

    /**
     * @var string
     */
    private $metadata;

    /**
     * Image constructor.
     *
     * @param Link|null $source
     * @param $src
     * @param string $alt
     * @param null $width
     * @param null $height
     */
    public function __construct($source = null, $src, $alt = "", $width = null, $height = null)
    {
        $this->src = $src;
        $this->source = $source;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Serialize image metadata
     *
     * @param $metadata
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = serialize($metadata);

        return $this;
    }

    /**
     * Get un-serialized metadata
     *
     * @return mixed
     */
    public function getMetadata()
    {
        return unserialize($this->metadata);
    }
}
