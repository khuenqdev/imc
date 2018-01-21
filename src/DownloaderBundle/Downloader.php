<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:10 PM
 */

namespace DownloaderBundle;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers;
use GuzzleHttp\Client as HttpClient;
use Monolog\Logger;
use QueueBundle\Queue;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Image as DomCrawlerImage;
use Symfony\Component\DomCrawler\Link as DomCrawlerLink;
use Symfony\Component\HttpFoundation\Response;

class Downloader
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Helpers
     */
    protected $helpers;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * List of HTML elements contain texts
     *
     * @var array
     */
    private $elementsContainText = [
        'meta[name=description]',
        'meta[name=keywords]',
        'title',
        'a',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'p'
    ];

    /**
     * @var string
     */
    public $outputMessages = "";

    /**
     * Downloader constructor.
     * @param EntityManager $em
     * @param Queue $queue
     * @param Helpers $helpers
     */
    public function __construct(EntityManager $em, Queue $queue, Helpers $helpers, Logger $logger)
    {
        $this->em = $em;
        $this->queue = $queue;
        $this->helpers = $helpers;
        $this->logger = $logger;
        $this->client = new HttpClient([
            'timeout' => 3,
            'allow_redirects' => false,
            'verify' => false
        ]);
    }

    /**
     * @param $url
     * @throws \Exception
     */
    public function download($url)
    {
        // Reset output message string
        $this->outputMessages = "";

        try {
            $client = new HttpClient([
                'base_uri' => $url,
                'timeout' => 60,
                'allow_redirects' => false,
                'verify' => false
            ]);

            $response = $client->get($url);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $dom = new DomCrawler($response->getBody()->getContents(), $url);
                $this->fetchContent($dom);
            }
        } catch (\Exception $e) {
            $this->saveLog("[Downloader] At line {$e->getLine()}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch content of the DOM document
     *
     * @param DomCrawler $dom
     * @return $this
     */
    public function fetchContent(DomCrawler $dom)
    {
        $this->extractLinks($dom);
        $this->downloadImages($dom);

        return $this;
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Download images from the DOM content
     *
     * @param DomCrawler $dom
     * @return $this
     */
    protected function downloadImages(DomCrawler $dom)
    {
        $imgElements = $dom->filter('img')->images();

        /** @var DomCrawlerImage $image */
        foreach ($imgElements as $image) {
            try {
                $this->helpers->image->download($dom->getUri(), $image);
            } catch (\Exception $e) {
                $this->saveLog("[Downloader] downloadImages() at line {$e->getLine()}: {$e->getMessage()}");
                $this->outputMessages .= "<info>[Downloader] downloadImages() at line {$e->getLine()}: {$e->getMessage()}</info> \n";
            }
        }

        return $this;
    }

    /**
     * Extract links from the page
     *
     * @param DomCrawler $dom
     * @return $this
     */
    protected function extractLinks(DomCrawler $dom)
    {
        // Extract page text, used for relevance calculation
        $pageText = $this->extractText($dom);
        $linkElements = $dom->filter('a')->links();

        /** @var DomCrawlerLink $domLink */
        foreach ($linkElements as $domLink) {
            $linkUrl = $this->helpers->url->parse($domLink->getUri(), $dom->getUri());

            if (!empty($linkUrl)) {
                $linkTitle = trim($domLink->getNode()->textContent);
                $relevance = $this->calculateRelevance($dom->getUri(), $linkUrl, $pageText, $linkTitle);

                // Create a database link entity if not exist and add new link to the queue
                $this->saveLinkToDatabase($linkUrl, $linkTitle, $relevance);
            }
        }

        return $this;
    }

    /**
     * Save link to database
     *
     * @param $linkUrl
     * @param $linkTitle
     * @param $linkRelevance
     */
    protected function saveLinkToDatabase($linkUrl, $linkTitle, $linkRelevance)
    {
        $link = $this->em->getRepository(Link::class)->findOneBy(['url' => $linkUrl]);

        if (!$link) {
            $link = new Link($linkUrl, $linkTitle, $linkRelevance);

            try {
                $this->em->persist($link);
                $this->em->flush($link);
                $this->em->refresh($link);
            } catch (\Exception $e) {
                $this->saveLog("[Downloader] saveLinkToDatabase() at line {$e->getLine()}: {$e->getMessage()}");
                $this->outputMessages .= "<info>[Downloader] saveLinkToDatabase() at line {$e->getLine()}: {$e->getMessage()}</info>\n";
            }
        }

        $this->queue->addLink($link);
    }

    /**
     * Extract page's text
     * @param DomCrawler $dom
     * @return string
     */
    protected function extractText(DomCrawler $dom)
    {
        $text = "";

        foreach ($this->elementsContainText as $tagName) {
            $textValues = $dom->filter($tagName)->extract(['_text']);
            $text .= implode("\n", $textValues) . "\n";
        }

        return $text;
    }

    /**
     * Calculate relevance score between a page and a link in it
     * Relevance calculation depends on 4 factors:
     *      - Url of the page
     *      - Url of the link
     *      - Page's text content
     *      - Link's title description
     *
     * @param string $pageUrl
     * @param string $linkUrl
     * @param string $pageText
     * @param string $linkTitle
     * @return float
     */
    protected function calculateRelevance($pageUrl, $linkUrl, $pageText, $linkTitle)
    {
        $score = 0;

        if (parse_url($pageUrl, PHP_URL_HOST) === parse_url($linkUrl, PHP_URL_HOST)) {
            $score++;
        }

        $linkTitleKeywords = $this->helpers->keyword->extract($linkTitle);
        $criteria = count($linkTitleKeywords) + 1;

        foreach ($linkTitleKeywords as $keyword) {
            $tf = $this->helpers->keyword->countWordOccurrence($keyword, $pageText);

            if ($tf > 1) {
                $score++;
            }
        }

        return (float)$score / $criteria;
    }

    /**
     * Log error messages and refresh/reopen entity manager
     *
     * @param $message
     */
    protected function saveLog($message)
    {
        $this->logger->debug($message);
    }
}