<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UrlQueue
 */
class UrlQueue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $parentId;

    /**
     * @var int
     */
    private $seedId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $urlTitle;

    /**
     * @var string
     */
    private $host;

    /**
     * @var float
     */
    private $relevance;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return UrlQueue
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set seedId
     *
     * @param integer $seedId
     * @return UrlQueue
     */
    public function setSeedId($seedId)
    {
        $this->seedId = $seedId;

        return $this;
    }

    /**
     * Get seedId
     *
     * @return integer 
     */
    public function getSeedId()
    {
        return $this->seedId;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return UrlQueue
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set urlTitle
     *
     * @param string $urlTitle
     * @return UrlQueue
     */
    public function setUrlTitle($urlTitle)
    {
        $this->urlTitle = $urlTitle;

        return $this;
    }

    /**
     * Get urlTitle
     *
     * @return string 
     */
    public function getUrlTitle()
    {
        return $this->urlTitle;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return UrlQueue
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set relevance
     *
     * @param float $relevance
     * @return UrlQueue
     */
    public function setRelevance($relevance)
    {
        $this->relevance = $relevance;

        return $this;
    }

    /**
     * Get relevance
     *
     * @return float 
     */
    public function getRelevance()
    {
        return $this->relevance;
    }
}
