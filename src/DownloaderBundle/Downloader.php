<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 8:10 PM
 */

namespace DownloaderBundle;

use AppBundle\Entity\Link;
use AppBundle\Entity\Report;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers;
use GuzzleHttp\Client as HttpClient;
use JonnyW\PhantomJs\Client as PhantomClient;
use Monolog\Logger;
use QueueBundle\Queue;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Image as DomCrawlerImage;
use Symfony\Component\DomCrawler\Link as DomCrawlerLink;
use Symfony\Component\HttpFoundation\Response;

class Downloader
{
    const ALGO_DFS = 'dfs'; // depth-first search
    const ALGO_BFS = 'bfs'; // breadth-first search
    const ALGO_BEFS = 'befs'; // best-first search

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
     * @var Report
     */
    protected $report;

    /**
     * List of HTML elements contain texts
     *
     * @var array
     */
    protected $elementsContainText = [
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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Crawling algorithm, has an impact on how a link is put on the queue
     *
     * @var string
     */
    protected $algorithm = self::ALGO_BEFS;

    /**
     * @var string
     */
    public $outputMessages = "";

    /**
     * Downloader constructor.
     * @param EntityManager $em
     * @param Queue $queue
     * @param Helpers $helpers
     * @param Logger $logger
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, Queue $queue, Helpers $helpers, Logger $logger, ContainerInterface $container)
    {
        $this->em = $em;
        $this->queue = $queue;
        $this->helpers = $helpers;
        $this->logger = $logger;
        $this->report = new Report();
        $this->container = $container;
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
            $client = PhantomClient::getInstance();
            $client->getEngine()->addOption('--ignore-ssl-errors=true');
            $client->getEngine()->addOption('--load-images=true');
            $request = $client->getMessageFactory()
                ->createRequest($url, 'GET');
            $request->setDelay(15);

            $response = $client->getMessageFactory()->createResponse();
            $client->send($request, $response);

            if ($response->getStatus() == Response::HTTP_OK) {
                $dom = new DomCrawler($response->getContent(), $url);
                $this->fetchContent($dom);
            }
        } catch (\Exception $e) {
            $this->saveLog("[Downloader] {$e->getMessage()}");
            $this->saveLog($e->getTraceAsString());
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
     * Get download report
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set algorithm
     *
     * @param $algorithm
     * @return $this
     * @throws \Exception
     */
    public function setAlgorithm($algorithm)
    {
        if ($algorithm !== self::ALGO_DFS && $algorithm !== self::ALGO_BFS && $algorithm !== self::ALGO_BEFS) {
            throw new \Exception('Invalid algorithm supplied for the crawler!');
        }

        $this->algorithm = $algorithm;

        return $this;
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
        $noOfImages = 0;
        $noOfExifImages = 0;

        /** @var DomCrawlerImage $image */
        foreach ($imgElements as $image) {
            try {
                $downloadedImage = $this->helpers->image->download($dom->getUri(), $image);

                if (!is_null($downloadedImage)) {
                    if ($downloadedImage->isExifLocation) {
                        $noOfExifImages++;
                    } else {
                        $noOfImages++;
                    }
                }

            } catch (\Exception $e) {
                $this->saveLog("[Downloader] downloadImages(): {$e->getLine()}: {$e->getMessage()}");
                $this->saveLog($e->getTraceAsString());
                $this->outputMessages .= "<info>[Downloader] downloadImages(): {$e->getMessage()}</info> \n";
            }
        }

        $this->report->noOfImages += $noOfImages;
        $this->report->noOfExifImages += $noOfExifImages;

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
        $noOfLinks = 0;

        // If this is a depth-first search we sort the link elements in reverse order before processing
        if ($this->algorithm === self::ALGO_DFS) {
            krsort($linkElements);
        }

        /** @var DomCrawlerLink $domLink */
        foreach ($linkElements as $domLink) {
            //$linkUrl = $this->helpers->url->parse($domLink->getUri(), $dom->getUri());
            $linkUrl = $domLink->getUri();

            if (!empty($linkUrl)) {
                $linkTitle = trim($domLink->getNode()->textContent);

                if ($this->algorithm === self::ALGO_BEFS) {
                    $relevance = $this->calculateRelevance($dom->getUri(), $linkUrl, $pageText, $linkTitle);
                } else {
                    $linkHost = parse_url($linkUrl, PHP_URL_HOST);
                    $relevance = (int) !$this->isHostBanned($linkHost);
                }

                // Ignore completely irrelevant links
                if ($relevance == 0) {
                    continue;
                }

                // Create a database link entity if not exist and add new link to the queue
                $this->saveLinkToDatabase($linkUrl, $linkTitle, $relevance);
                $noOfLinks++;
            }
        }

        $this->report->noOfLinks += $noOfLinks;

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

                switch ($this->algorithm) {
                    case self::ALGO_BFS:
                        $this->queue->addLinkToBottom($link);
                        break;
                    case self::ALGO_DFS:
                        $this->queue->addLinkToTop($link);
                        break;
                    default:
                        $this->queue->addLink($link); // Follow priority queue rule
                        break;
                }
            } catch (\Exception $e) {
                $this->saveLog("[Downloader] saveLinkToDatabase(): {$e->getLine()}: {$e->getMessage()}");
                $this->saveLog($e->getTraceAsString());
                $this->outputMessages .= "<info>[Downloader] saveLinkToDatabase(): {$e->getMessage()}</info>\n";
            }
        }
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
        // Get a list of banned hosts (mostly social networks)
        $linkHost = parse_url($linkUrl, PHP_URL_HOST);

        // If the host of the link is in the list of banned host, we automatically assume it is irrelevant
        if ($this->isHostBanned($linkHost)) {
            return 0;
        }

        $score = 0;
        $pageHost = parse_url($pageUrl, PHP_URL_HOST);

        if ($pageHost === $linkHost) {
            $score++;
        }

        $linkTitleKeywords = $this->helpers->keyword->extract($linkTitle);
        $criteria = count($linkTitleKeywords) + 1; // Number of keywords, plus 1 from host matching

        foreach ($linkTitleKeywords as $keyword) {
            $tf = $this->helpers->keyword->countWordOccurrence($keyword, $pageText);

            if ($tf > 1) {
                $score++;
            }
        }

        return (float)$score / $criteria;
    }

    /**
     * Check if a host is banned
     *
     * @param $host
     * @return bool
     */
    protected function isHostBanned($host) {
        $bannedHosts = $this->container->getParameter('banned_hosts');

        foreach($bannedHosts as $banned) {
            if (strpos($host, $banned) !== false) {
                return true;
            }
        }

        return false;
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
