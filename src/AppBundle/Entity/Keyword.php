<?php

namespace AppBundle\Entity;

/**
 * Keyword
 */
class Keyword
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $word;

    /**
     * @var float
     */
    private $tfIdf;

    /**
     * @var Page
     */
    private $page;

    /**
     * Keyword constructor.
     *
     * @param string $word
     * @param float $tfIdf
     */
    public function __construct($word = '', $tfIdf = 0.0)
    {
        $this->word = $word;
        $this->tfIdf = $tfIdf;
    }

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
     * Set word
     *
     * @param string $word
     *
     * @return Keyword
     */
    public function setWord($word)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * Get word
     *
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * Set tf-idf
     *
     * @param float $tfIdf
     *
     * @return Keyword
     */
    public function setTfIdf($tfIdf)
    {
        $this->tfIdf = $tfIdf;

        return $this;
    }

    /**
     * Get tf-idf
     *
     * @return float
     */
    public function getTfIdf()
    {
        return $this->tfIdf;
    }

    /**
     * Set page
     *
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }
}

