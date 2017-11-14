<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 25-Oct-17
 * Time: 22:22
 */

namespace AppBundle\Components;

use AppBundle\Entity\Image;
use AppBundle\Entity\Keyword;
use AppBundle\Entity\Link;
use AppBundle\Entity\Page;
use AppBundle\Entity\Text;
use AppBundle\Repository\KeywordRepository;
use AppBundle\Services\Helpers\Keyword as KeywordHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use DOMElement;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\Helpers\Url as UrlHelper;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class Downloader
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var KeywordHelper
     */
    private $keywordHelper;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var bool
     */
    protected $outputToCommandLine = false;

    /**
     * List of HTML elements contain texts
     *
     * @var array
     */
    private $elementsContainText = ['title', 'h1', 'h2', 'h3', 'h4', 'h5', 'p'];

    /**
     * Downloader constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityManager $em
     * @param KeywordHelper $keywordHelper
     * @param UrlHelper $urlHelper
     */
    public function __construct(LoggerInterface $logger, EntityManager $em, KeywordHelper $keywordHelper, UrlHelper $urlHelper)
    {
        $this->em = $em;
        $this->keywordHelper = $keywordHelper;
        $this->urlHelper = $urlHelper;
        $this->logger = $logger;
    }

    /**
     * Whether the downloader output relevant information to commandline interface
     *
     * @param $outputToCommandLine
     * @return $this
     */
    public function setOutputToCommandLine($outputToCommandLine)
    {
        $this->outputToCommandLine = $outputToCommandLine;

        return $this;
    }

    /**
     * Retrieve a page's data
     *
     * @param Page $page
     */
    public function download(Page &$page)
    {
        // Bypass the current page if its URL is empty
        if (!$page->getUrl()) {
            $this->logger->notice("Unable to fetch page: Page URL reference must not be empty!");
            return;
        }

        // Create a new HTTP client
        $client = new HttpClient();

        // Request for resource
        try {
            $resource = $client->request(Request::METHOD_GET, $page->getUrl());

            // Check if our request returns valid response
            if ($resource->getStatusCode() == Response::HTTP_OK) {

                // Create DOM object from page's HTML content
                $dom = new \DOMDocument();
                @$dom->loadHTML($resource->getBody());

                if ($dom) {
                    // Update page data
                    if ($titleElement = $dom->getElementsByTagName('title')->item(0)) {
                        $page->setTitle($titleElement->textContent);
                    }

                    // Extract text content
                    $page->setText($this->extractText($dom));

                    // Extract keywords
                    $page->setKeywords($this->extractKeywords($page));

                    // Extract links on page and save them to the database
                    $page->setLinks($this->extractLinks($page, $dom));

                    // Download images on the page
                    //$this->downloadImages($page, $dom);
                }
            }

            // Save page changes and flush all entities
            $this->em->persist($page);
            $this->em->flush();
        } catch (\Exception $e) {
            if ($this->outputToCommandLine) {
                echo '[Downloader Warning] ' . $e->getMessage() . "\n";
                //echo $e->getTraceAsString() . "\n";
            }
        }
    }

    /**
     * Extract text and keywords of the page
     *
     * @param \DOMDocument $dom
     * @return $text
     */
    protected function extractText(\DOMDocument $dom)
    {
        // Initialize extracted text content
        $text = "";

        // Get text from meta description
        $metas = $dom->getElementsByTagName('meta');

        /** @var DOMElement $meta */
        foreach ($metas as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $text .= preg_replace("/[\W]+/", " ", strip_tags($meta->getAttribute('content'))) . " ";
                break;
            }
        }

        // Append text from body elements
        foreach ($this->elementsContainText as $tagName) {
            $elements = $dom->getElementsByTagName($tagName);
            if ($elements->length > 0) {
                /** @var DOMElement $element */
                foreach ($elements as $element) {
                    $text .= preg_replace("/[\W]+/", " ", strip_tags($element->textContent)) . " ";
                }
            }
        }

        // Save text content
        $text = new Text($text);
        $this->em->persist($text);

        return $text;
    }

    /**
     * Extract keywords
     *
     * @param Page $page
     * @return ArrayCollection
     */
    protected function extractKeywords(Page $page)
    {
        // Extract keywords from the page
        $extracted = $this->keywordHelper->extractKeywords($page->getText()->getContent());

        // Initialize keyword collection
        $keywords = new ArrayCollection();

        /** @var KeywordRepository $repo */
        $repo = $this->em->getRepository(Keyword::class);

        // Check if extracted keyword exists in the database
        foreach ($extracted as $keyword => $tfidf) {
            /** @var Keyword $keywordObj */
            try {
                $keywordObj = $repo->findOneBy(['word' => $keyword, 'page' => $page]);
                // If the keyword already exists, update its tfidf score
                if ($keywordObj) {
                    $keywordObj->setTfIdf($tfidf);
                } else {
                    // Otherwise, create a new keyword in the database
                    $keywordObj = new Keyword($keyword, $tfidf);
                    $keywordObj->setPage($page);
                }

                $this->em->persist($keywordObj);
                $keywords->add($keywordObj);
            } catch (NoResultException $e) {
                echo '[Keyword Warning]' . $e->getMessage() . "\n";
            }
        }

        return $keywords;
    }

    /**
     * Extract page's URLs, calculate their relevance and add them to the queue
     *
     * @param Page $page The page contains the link
     * @param \DOMDocument $dom Object model of the page content
     * @return Collection
     */
    protected function extractLinks(Page $page, \DOMDocument $dom)
    {
        $baseUrl = $page->getUrl();

        // Initialize a collection of links
        $links = new ArrayCollection();

        // Get link repository
        $linkRepo = $this->em->getRepository(Link::class);

        // Get link elements from the document object model
        $linkElements = $dom->getElementsByTagName('a');

        /** @var \DOMElement $linkElement */
        foreach ($linkElements as $linkElement) {
            // Extract the Hyper Reference of the link element
            $href = $linkElement->getAttribute('href');

            // If the link element has Hyper Reference
            if ($href) {
                // Parse the Hyper Reference as URL
                $url = $this->urlHelper->parse($href, $baseUrl);

                // Calculate link relevance
                $relevance = $this->computeRelevance($page, $url, $linkElement->textContent);

                // Check if a link with the same URL already exists
                try {
                    $dbLink = $linkRepo->findOneBy(['url' => $url]);
                    if (!$dbLink && $relevance > 0.0) {
                        // If not, create new link entity object
                        $link = new Link($url, $linkElement->textContent, $relevance);
                        $link->setPage($page);

                        // Persist and add the link to link collection
                        $this->em->persist($link);
                        $links->add($link);
                    }
                } catch (NoResultException $e) {
                    echo "[Link Warning] " . $e->getMessage() . "\n";
                }
            }
        }

        return $links;
    }

    /**
     * Download images from a page
     *
     * @param Page $page
     * @param \DOMDocument $dom
     */
    protected function downloadImages(Page &$page, \DOMDocument $dom)
    {
        // Get image elements
        $imgElements = $dom->getElementsByTagName('img');

        if ($imgElements->length > 0) {
            /** @var \DOMElement $element */
            foreach ($imgElements as $element) {

                // Parse image src attribute to get actual image URL
                $src = $this->urlHelper->parse($element->getAttribute('src'), $page->getUrl());

                if (!empty($src)) {
                    // Create new image object
                    $image = new Image($src, $element->getAttribute('alt'), $page);
                }
            }
        }
    }

    /**
     * Compute relevance of a link
     *
     * @param Page $page
     * @param $url
     * @param $title
     * @return float
     */
    protected function computeRelevance(Page $page, $url, $title)
    {
        return 1.0;
    }

}