<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/12/2017
 * Time: 3:07 PM
 */

namespace AppBundle\Services\Helpers;

use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use \AppBundle\Entity\Keyword as KeywordEntity;

class Keyword
{
    /**
     * Limit the number of keywords
     */
    const NO_OF_KEYWORDS = 10;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Pattern for text purification
     *
     * @var array
     */
    private $replacePatterns = [
        '/\&lt;/' => '',
        '/\&gt;/' => '',
        '/\&nbsp;/' => ' ',
        '/\&quot;/' => '',
        '/\&amp;/' => '',
        '/\&lsquo;/' => '',
        '/\&rsquo;/' => '',
        '/\&ldquo;/' => '',
        '/\&rdquo;/' => '',
        '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u' => '', // Literally a single quote
        '/[“”«»„"]/u' => '', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        '/\*/' => ' ',
        '/\:/' => ' ',
        '/\,/' => ' ',
        '/\“‘/' => ' ',
        '/\“/' => ' ',
        '/\”/' => ' ',
        '/\’/' => ' ',
        '/\d/' => ' ',
        '/\\\/' => '',
        '/\+/' => ' ',
        '/\#/' => ' ',
        '/\{/' => ' ',
        '/\}/' => ' ',
        '/&/' => ' ',
        '/\~/' => ' ',
        '/\>/' => ' ',
        '/\</' => ' ',
        '/\=/' => ' ',
        '/\@/' => ' ',
        '/\`/' => ' ',
        '/\$/' => ' ',
        '/\//' => ' ',
        '/\£/' => ' ',
        '/\^/' => ' ',
        '/\%/' => ' ',
        '/\|/' => ' ',
        '/\t/' => ' ',
        '/\n/' => ' ',
        '/\r/' => ' ',
        '/\d+[s]/' => ' ',
        //'/ \w{1,2} /' => ' ',
        //'/(\b.{1,2}\s)/' => ' ' // Removing short words
    ];

    /**
     * Keyword helper constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Extract keywords from a text
     *
     * @param $text
     * @return array
     */
    public function extractKeywords($text)
    {
        // 1. Purify the text
        $text = $this->purify($text);

        // 2. Extract word tokens from the text
        $tokens = $this->tokenize($text);

        // 3. Normalize all tokens (convert to lowercase)
        $normalizedTokens = $this->normalize($tokens);

        // 4. Calculate tf-idf
        $tfIdf = $this->calculateTfIdf($normalizedTokens);

        // 5. Sort the tf-idf descending
        arsort($tfIdf);

        // 6. Select the top 10 tokens with the highest tf-idf score as keywords
        return array_slice($tfIdf, 0, self::NO_OF_KEYWORDS, true);
    }

    /**
     * Purify a text
     *
     * @param $text
     * @return mixed
     */
    public function purify($text)
    {
        foreach ($this->replacePatterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }

    /**
     * Break a text into a set of word tokens
     *
     * @param $text
     * @return array
     */
    public function tokenize($text)
    {
        $tokens = preg_split('/[\W]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }

    /**
     * Normalize a set of word tokens (convert to lowercase characters)
     *
     * @param $tokens
     * @return array
     */
    public function normalize($tokens)
    {
        return array_map('mb_strtolower', $tokens);
    }

    /**
     * Calculate term frequency within the current document
     *
     * @param $tokens
     * @return array
     */
    public function calculateTf($tokens)
    {
        $totalNoOfTokens = count($tokens);
        $counts = array_count_values($tokens);

        $termFrequencies = array_map(function ($count) use ($totalNoOfTokens) {
            return (float)($count / $totalNoOfTokens);
        }, $counts);

        return $termFrequencies;
    }

    /**
     * Calculate inverse document frequency of the current document
     *
     * @param $tokens
     * @return array
     */
    public function calculateIdf($tokens)
    {
        $idf = [];
        $noOfPages = $this->em->getRepository(Page::class)
                ->getNoOfCrawledPages() + 1;

        foreach ($tokens as $token) {
            $noOfPagesContainToken = $this->em->getRepository(KeywordEntity::class)
                    ->getNoOfPageContainsKeyword($token) + 1;

            $idf[$token] = log10((float)($noOfPages / $noOfPagesContainToken));
        }

        return $idf;
    }

    /**
     * Calculate tf-idf of the current document
     *
     * @param $tokens
     * @return array
     */
    public function calculateTfIdf($tokens)
    {
        $tf = $this->calculateTf($tokens);
        $idf = $this->calculateIdf($tokens);

        $tfIdf = array_map(function ($x, $y) {
            return $x * $y;
        }, $tf, $idf);

        return array_combine(array_keys($tf), $tfIdf);
    }
}