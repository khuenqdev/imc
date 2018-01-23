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
     * @var bool
     */
    public $isExifLocation;

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
     * @var bool
     */
    public $geoparsed;

    /**
     * Flag to check whether the location is correct (if it comes from EXIF then it is automatically correct)
     *
     * @var bool
     */
    public $isLocationCorrect;

    /**
     * @var string
     */
    private $metadata;

    /**
     * Image constructor.
     *
     * @param $src
     * @param string $alt
     * @param null $width
     * @param null $height
     */
    public function __construct($src, $alt = "", $width = null, $height = null)
    {
        $this->src = $src;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
        $this->geoparsed = false;
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
