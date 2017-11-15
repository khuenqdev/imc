<?php

namespace AppBundle\Entity;

/**
 * Stopword
 */
class Stopword
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
     * Stopword constructor.
     *
     * @param $word
     */
    public function __construct($word)
    {
        $this->word = $word;
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
     * @return Stopword
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
}

