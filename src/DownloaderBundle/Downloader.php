<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:10 PM
 */

namespace DownloaderBundle;

use AppBundle\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
     * @param Registry $doctrine
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
        $promise = $this->client->getAsync($link->url);

        $promise->then(
            function (ResponseInterface $res) use ($link) {
                if ($res->getStatusCode() == Response::HTTP_OK) {
                    $this->fetchContent($res, $link);
                }
            },
            function (RequestException $e) {
                $this->saveLog("[Downloader Error] downloadAsync() " . $e->getMessage());
            }
        );
    }

    /**
     * Download content of a page (synchronous)
     *
     * @param Link $link
     * @return bool|string
     */
    public function download(Link &$link)
    {
        try {

            $res = $this->client->get($link->url);

            if ($res->getStatusCode() == Response::HTTP_OK) {
                $this->fetchContent($res, $link);
                $this->markAsVisited($link);
                return true;
            }

        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] download() " . $e->getMessage());
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
    protected function fetchContent(ResponseInterface $response, Link $link)
    {
        $html = $response->getBody()->getContents();
        $dom = new Crawler($html, $link->url);
        $text = $this->extractText($dom);
        $this->extractImages($dom, $link);
        $this->extractLinks($dom, $link, $text);
    }

    /**
     * Extract links
     *
     * @return $this
     */
    protected function extractLinks(Crawler $dom, Link $link, $text)
    {
        $links = $dom->filter('a')->links();

        /** @var DomLink $domLink */
        foreach ($links as $domLink) {
            $url = $this->helpers->url->parse($domLink->getUri(), $link->url);

            if (!empty($url)) {
                $title = empty($domLink->getNode()->textContent)
                    ? trim($domLink->getNode()->getAttribute('title'))
                    : trim($domLink->getNode()->textContent);
                $relevance = $this->calculateRelevance($dom, $url, $title, $text);

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
    protected function extractImages(Crawler $dom, Link $link)
    {
        $imgElements = $dom->filter('img')->images();

        /** @var Image $element */
        foreach ($imgElements as $element) {
            $src = $element->getUri();
            $alt = $element->getNode()->getAttribute('alt');
            $this->helpers->image->download($element->getNode(), $link, $src, $alt);
            $this->errorMessage = $this->helpers->image->getErrorMessage();
        }

        return $this;
    }

    /**
     * Extract page's text
     * @return string
     */
    protected function extractText(Crawler $dom)
    {
        $text = "";

        foreach ($this->elementsContainText as $tagName) {
            $textValues = $dom->filter($tagName)->extract(['_text']);
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
    protected function calculateRelevance(Crawler $dom, $uri, $title, $text)
    {
        $score = 0;

        if (parse_url($dom->getUri(), PHP_URL_HOST) === parse_url($uri, PHP_URL_HOST)) {
            $score++;
        }

        $linkTitleKeywords = $this->helpers->keyword->extract($title);
        $criteria = count($linkTitleKeywords) + 1;

        foreach ($linkTitleKeywords as $keyword) {
            $tf = $this->helpers->keyword->countWordOccurrence($keyword, $text);

            if ($tf > 1) {
                $score++;
            }
        }

        return (float)$score / $criteria;
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
            $this->em->refresh($link);
        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] saveLinkToDatabase() " . $e->getMessage());
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
            $this->em->refresh($link);
        } catch (\Exception $e) {
            $this->saveLog("[Downloader Error] markAsVisited() " . $e->getMessage());
        }
    }

    /**
     * Log error messages and refresh/reopen entity manager
     *
     * @param $message
     */
    protected function saveLog($message)
    {
        $this->errorMessage = $message . "\n";
        $this->logger->debug($message);
    }
}