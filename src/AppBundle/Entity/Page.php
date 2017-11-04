<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var Page
     */
    private $parent;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Text
     */
    private $text;

    /**
     * @var Collection
     */
    private $keywords;

    /**
     * @var string
     */
    private $html;

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var Collection
     */
    private $links;

    /**
     * @var Collection
     */
    private $images;

    /**
     * Page constructor.
     *
     * @param $url
     * @param $title
     * @param null $parent
     */
    public function __construct($url, $title = '', $parent = null)
    {
        $this->url = $url;

        // Determine host from URL
        $host = parse_url($url, PHP_URL_HOST);
        $this->setHost($host);

        $this->title = $title;
        $this->parent = $parent;

        $this->keywords = new ArrayCollection();
        $this->links = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

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
     * Set parent
     *
     * @param integer $parent
     * @return Page
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Page
     */
    public function getParent()
    {
        return $this->parent;
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
     * @param Text $text
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
     * @return Text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set keywords
     *
     * @param Collection $keywords
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
     * @return Collection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Add a keyword
     *
     * @param Keyword $keyword
     * @return $this
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
            $keyword->setPage($this);
        }

        return $this;
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
    public function setLinks(Collection $links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return Collection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Add a link to page entity
     *
     * @param Link $link
     * @return $this
     */
    public function addLink(Link $link)
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setPage($this);
        }

        return $this;
    }

    /**
     * Set images
     *
     * @param $images
     * @return $this
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return ArrayCollection|Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add an image
     *
     * @param Image $image
     * @return $this
     */
    public function addImage(Image $image)
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setPage($this);
        }

        return $this;
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
