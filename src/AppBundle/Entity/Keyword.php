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
    private $tf;

    /**
     * @var float
     */
    private $idf;

    /**
     * @var Page
     */
    private $page;

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
     * Set tf
     *
     * @param float $tf
     *
     * @return Keyword
     */
    public function setTf($tf)
    {
        $this->tf = $tf;

        return $this;
    }

    /**
     * Get tf
     *
     * @return float
     */
    public function getTf()
    {
        return $this->tf;
    }

    /**
     * Set idf
     *
     * @param float $idf
     *
     * @return Keyword
     */
    public function setIdf($idf)
    {
        $this->idf = $idf;

        return $this;
    }

    /**
     * Get idf
     *
     * @return float
     */
    public function getIdf()
    {
        return $this->idf;
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

