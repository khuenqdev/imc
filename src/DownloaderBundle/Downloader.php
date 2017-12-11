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
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use QueueBundle\Queue;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Component\DomCrawler\Link as DomLink;
use Symfony\Component\HttpFoundation\Response;

class Downloader
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var Helpers
     */
    protected $helpers;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var \stdClass
     */
    protected $page;

    /**
     * @var string
     */
    protected $errorMessage = "";

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
     * Download content of a page (asynchronous)
     *
     * @param Link $link
     */
    public function downloadAsync(Link $link)
    {
        $this->initialize($link);

        $promise = $this->client->getAsync($this->page->link->url);

        $promise->then(
            function (ResponseInterface $res) {
                if ($res->getStatusCode() == Response::HTTP_OK) {
                    $this->fetchContent($res);
                }
            },
            function (RequestException $e) {
                $this->saveLog("[Downloader Error] " . $e->getMessage());
            }
        );
    }

    /**
     * Download content of a page (synchronous)
     *
     * @param Link $link
     * @return bool|string
     */
    public function download(Link $link)
    {
        $this->initialize($link);

        try {

            $res = $this->client->get($this->page->link->url);

            if ($res->getStatusCode() == Response::HTTP_OK) {
                $this->fetchContent($res);
                $this->markAsVisited($link);
                return true;
            }

        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] " . $e->getMessage());
        }

        return false;
    }

    /**
     * Get error message (if any)
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Fetch content of the page from response
     *
     * @param ResponseInterface $response
     */
    protected function fetchContent(ResponseInterface $response)
    {
        $this->page->html = $response->getBody()->getContents();
        $this->page->dom = new Crawler($this->page->html, $this->page->link->url);
        $this->page->text = $this->extractText();
        $this->extractImages();
        $this->extractLinks();
    }

    /**
     * Extract links
     *
     * @return $this
     */
    protected function extractLinks()
    {
        $links = $this->page->dom->filter('a')->links();

        /** @var DomLink $domLink */
        foreach ($links as $domLink) {
            $url = $this->helpers->url->parse($domLink->getUri(), $this->page->link->url);

            if (!empty($url)) {
                $title = empty($domLink->getNode()->textContent)
                    ? trim($domLink->getNode()->getAttribute('title'))
                    : trim($domLink->getNode()->textContent);
                $relevance = $this->calculateRelevance($url, $title);

                // Create a database link entity if not exist and add new link to the queue
                if (!$this->em->getRepository(Link::class)->findOneBy(['url' => $url])) {
                    $link = $this->saveLinkToDatabase($url, $title, $relevance);
                    $this->queue->addLink($link);
                }
            }
        }

        return $this;
    }

    /**
     * Extract images
     *
     * @return $this
     */
    protected function extractImages()
    {
        $imgElements = $this->page->dom->filter('img')->images();

        /** @var Image $element */
        foreach ($imgElements as $element) {
            $src = $element->getUri();
            $alt = $element->getNode()->getAttribute('alt');
            $this->helpers->image->download($element->getNode(), $this->page->link, $src, $alt);
            $this->errorMessage = $this->helpers->image->getErrorMessage();
        }

        return $this;
    }

    /**
     * Extract page's text
     * @return string
     */
    protected function extractText()
    {
        $text = "";

        foreach ($this->elementsContainText as $tagName) {
            $textValues = $this->page->dom->filter($tagName)->extract(['_text']);
            $text .= implode("\n", $textValues) . "\n";
        }

        return $text;
    }

    /**
     * Calculate relevance score between a URL and the page contains it
     *
     * @param $uri
     * @param $title
     * @return float
     */
    protected function calculateRelevance($uri, $title)
    {
        $score = 0;

        if (parse_url($this->page->dom->getUri(), PHP_URL_HOST) === parse_url($uri, PHP_URL_HOST)) {
            $score++;
        }

        $linkTitleKeywords = $this->helpers->keyword->extract($title);
        $criteria = count($linkTitleKeywords) + 1;

        foreach ($linkTitleKeywords as $keyword) {
            $tf = $this->helpers->keyword->countWordOccurrence($keyword, $this->page->text);

            if ($tf > 1) {
                $score++;
            }
        }

        return (float)$score / $criteria;
    }

    /**
     * Get page cache from the downloader
     *
     * @return \stdClass
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Save link to database
     *
     * @param $url
     * @param $title
     * @param $relevance
     * @return Link
     */
    protected function saveLinkToDatabase($url, $title, $relevance)
    {
        $link = new Link($url, $title, $relevance);

        try {
            $this->em->persist($link);
            $this->em->flush($link);
        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] " . $e->getMessage());
        }

        return $link;
    }

    /**
     * Mark a link as visited
     *
     * @param Link $link
     */
    protected function markAsVisited(Link $link)
    {
        $link->visited = true;

        try {
            $this->em->persist($link);
            $this->em->flush($link);
        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] " . $e->getMessage());
        }
    }

    /**
     * Initialize downloader's page cache
     *
     * @param Link $link
     */
    protected function initialize(Link $link)
    {
        if (!$this->page) {
            $this->page = new \stdClass();
        }

        $this->page->link = $link;
        $this->page->html = null;
        $this->page->dom = null;
        $this->page->text = null;

        $this->errorMessage = "";
    }

    /**
     * Log error messages
     *
     * @param $message
     */
    protected function saveLog($message)
    {
        $this->errorMessage = $message . "\n";
        $this->logger->debug($message);
    }
}