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
     * @Serializer\Expose
     */
    public $type;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $alt;

    /**
     * @var bool
     * @Serializer\Expose
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
     * @Serializer\Expose
     */
    public $isLocationCorrect;

    /**
     * @var integer
     */
    public $geoparserRetries;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     * @Serializer\Expose
     */
    public $thumbnail;

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
        $this->geoparserRetries = 0;
        $this->extractDomain($src);
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
        $metadata = unserialize($this->metadata);

        return array_map(function($val) {
            if (is_array($val)) {
                return implode('|', $val);
            } elseif(is_object($val)) {
                return serialize($val);
            }

            return $val;
        }, $metadata);
    }

    /**
     * @Serializer\VirtualProperty(name="image_metadata")
     * @return mixed
     */
    public function getImageMetadata()
    {
        return $this->getMetadata();
    }

    /**
     * Extract image domain
     *
     * @param $src
     */
    private function extractDomain($src)
    {
        $host = parse_url($src, PHP_URL_HOST);

        if ($host && !empty($host)) {
            $domain = substr($host, strrpos($host, '.') + 1);
            $this->domain = $domain;
        }
    }
}
