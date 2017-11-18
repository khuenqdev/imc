<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:10 PM
 */

namespace DownloaderBundle;

use Doctrine\ORM\EntityManager;
use DownloaderBundle\Exception\DownloaderException;
use DownloaderBundle\Services\Helpers;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use MyProject\Proxies\__CG__\stdClass;
use Psr\Http\Message\ResponseInterface;
use QueueBundle\Queue;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
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
     * @var stdClass
     */
    protected $page;

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
    public function __construct(EntityManager $em, Queue $queue, Helpers $helpers)
    {
        $this->em = $em;
        $this->queue = $queue;
        $this->helpers = $helpers;
        $this->client = new HttpClient();
    }

    /**
     * Download content of a page (asynchronous)
     *
     * @param $url
     */
    public function downloadAsync($url)
    {
        $this->initializePage($url);

        $promise = $this->client->getAsync($url, ['timeout' => 3]);

        $promise->then(
            function (ResponseInterface $res) {
                if ($res->getStatusCode() == Response::HTTP_OK) {
                    $this->fetchContent($res);
                }
            },
            function (RequestException $e) {
                throw new DownloaderException($e->getResponse()->getStatusCode(), $e->getMessage());
            }
        );
    }

    /**
     * @param $url
     */
    public function download($url)
    {
        $this->initializePage($url);

        $res = $this->client->get($url, ['timeout' => 3]);

        if ($res->getStatusCode() == Response::HTTP_OK) {
            $this->fetchContent($res);
        }
    }

    /**
     * Fetch content of the page from response
     *
     * @param ResponseInterface $response
     */
    public function fetchContent(ResponseInterface $response)
    {
        $this->page->html = $response->getBody()->getContents();
        $this->page->dom = new Crawler($this->page->html, $this->page->url);
        $this->page->text = $this->extractText();
        $this->page->links = $this->extractLinks();
    }

    /**
     * Extract links
     */
    public function extractLinks()
    {
        $links = $this->page->dom->filter('a')->links();

        $linkData = [];

        /** @var Link $link */
        foreach ($links as $link) {
            $url = $link->getUri();
            $title = empty($link->getNode()->textContent)
                ? trim($link->getNode()->getAttribute('title'))
                : trim($link->getNode()->textContent);
            $relevance = $this->calculateRelevance($url, $title);

            $linkData[sha1($url)] = [
                'url' => $url,
                'title' => $title,
                'relevance' => $relevance
            ];

            $this->queue->addLink($url, $title, $relevance);
        }

        return $linkData;
    }

    /**
     * Extract page's text
     * @return string
     */
    public function extractText()
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
    public function calculateRelevance($uri, $title)
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
     * @return stdClass
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Initialize downloader's page cache
     */
    protected function initializePage($url)
    {
        $this->page = new \stdClass();

        $this->page->url = $url;
        $this->page->html = null;
        $this->page->dom = null;
        $this->page->text = null;
        $this->page->links = null;
    }
}