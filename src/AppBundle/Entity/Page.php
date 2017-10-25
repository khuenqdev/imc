<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 */
class Page
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
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var float
     */
    private $relevance;

    /**
     * @var string
     */
    private $html;

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var array
     */
    private $links;

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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * Set title
     *
     * @param string $title
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Page
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return Page
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set relevance
     *
     * @param float $relevance
     * @return Page
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

    /**
     * Set raw HTML content
     *
     * @param $html
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Retrieve raw HTML content of a page
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set links
     *
     * @param $links
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set DOM object
     *
     * @param $dom
     * @return $this
     */
    public function setDom($dom)
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * Get DOM object
     *
     * @return \DOMDocument
     */
    public function getDom()
    {
        return $this->dom;
    }
}
