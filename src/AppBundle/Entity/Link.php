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
    private $id;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var bool
     */
    private $visited;

    /**
     * @var float
     */
    private $relevance;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Link
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
     * Set title
     *
     * @param string $title
     *
     * @return Link
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
     * Set relevance
     *
     * @param float $relevance
     *
     * @return Link
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
     * Set page
     *
     * @param Page $page
     * @return $this
     */
    public function setPage(Page $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set visited
     *
     * @param bool $visited
     * @return $this
     */
    public function setVisited($visited)
    {
        $this->visited = $visited;

        return $this;
    }

    /**
     * Get visited
     *
     * @return bool
     */
    public function getVisited()
    {
        return $this->visited;
    }

    /**
     * Alias of getVisited()
     *
     * @return bool
     */
    public function isVisited()
    {
        return $this->getVisited();
    }
}

