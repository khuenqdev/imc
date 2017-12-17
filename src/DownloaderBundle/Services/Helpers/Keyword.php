<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 1:34 AM
 */

namespace DownloaderBundle\Services\Helpers;


use AppBundle\Entity\Stopword;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

class Keyword
{
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
     * @var EntityManager
     */
    protected $em;

    /**
     * Keyword helper constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Extract keywords from a string
     *
     * @param $string
     * @return mixed
     */
    public function extract($string)
    {
        $string = $this->normalize($string);
        $string = $this->purify($string);
        $tokens = $this->tokenize($string);

        return $this->filterStopwords($tokens);
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
     * Normalize a string
     *
     * @param $string
     * @return mixed|string
     */
    public function normalize($string)
    {
        return mb_strtolower($string);
    }

    /**
     * Remote stop words
     *
     * @param $tokens
     * @return mixed
     */
    public function filterStopwords($tokens)
    {
        $repo = $this->em->getRepository(Stopword::class);

        foreach ($tokens as $idx => $token) {
            if ($repo->findOneBy(['hash' => sha1($token)])) {
                unset($tokens[$idx]);
            }
        }

        return $tokens;
    }

    /**
     * Count the number of times a word occurs in a text
     *
     * @param $word
     * @param $text
     * @return int
     */
    public function countWordOccurrence($word, $text)
    {
        $normalized = mb_strtolower($text);
        return mb_substr_count($normalized, mb_strtolower($word));
    }

}