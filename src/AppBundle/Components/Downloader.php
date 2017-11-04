<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 25-Oct-17
 * Time: 22:22
 */

namespace AppBundle\Components;

use AppBundle\Entity\Link;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\Helpers\String as StringHelper;
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
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * Downloader constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityManager $em
     * @param StringHelper $stringHelper
     * @param UrlHelper $urlHelper
     */
    public function __construct(LoggerInterface $logger, EntityManager $em, StringHelper $stringHelper, UrlHelper $urlHelper)
    {
        $this->em = $em;
        $this->stringHelper = $stringHelper;
        $this->urlHelper = $urlHelper;
        $this->logger = $logger;
    }

    /**
     * Retrieve a page's data
     *
     * @param Page $page
     */
    public function download(Page &$page)
    {
        if (!$page->getHtml()) {

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
                        $page->setHtml($resource->getBody())
                            ->setDom($dom);

                        if ($titleElement = $dom->getElementsByTagName('title')->item(0)) {
                            $page->setTitle($titleElement->textContent);
                        }

                        // Extract text content and keywords
                        $this->extractTextAndKeywords($page, $dom);

                        // Extract links on page and save them to the database
                        $this->extractLinks($page, $dom);

                        // Download images on the page
                        $this->downloadImages($page, $dom);
                    }
                }
            } catch (\Exception $e) {

            }

        }

        // Save page changes and flush all entities
        $this->em->persist($page);
        $this->em->flush();
    }

    /**
     * Extract page's URLs, calculate their relevance and add them to the queue
     *
     * @param Page $page
     * @param \DOMDocument $dom
     */
    protected function extractLinks(Page &$page, \DOMDocument $dom)
    {
        $linkElements = $dom->getElementsByTagName('a');

        /** @var \DOMElement $linkElement */
        foreach ($linkElements as $linkElement) {
            // Parse the Hyper Reference to obtain valid URL
            $url = $this->urlHelper->parse($linkElement->getAttribute('href'), $page->getUrl());

            if ($url) {
                // @todo Calculate URL relevance
                $relevance = 1;

                // Create a link entity, persist and associate it to the page
                $this->createLink($url, $linkElement->nodeValue, $relevance, $page);
            }
        }
    }

    /**
     * Download images from a page
     *
     * @param Page $page
     * @param \DOMDocument $dom
     */
    protected function downloadImages(Page &$page, \DOMDocument $dom)
    {

    }

    /**
     * Extract text and keywords of the page
     *
     * @param Page $page
     * @param \DOMDocument $dom
     */
    protected function extractTextAndKeywords(Page &$page, \DOMDocument $dom)
    {

    }

    /**
     * Create a new link
     *
     * @param $url
     * @param $title
     * @param $relevance
     * @param Page $page
     */
    protected function createLink($url, $title, $relevance, Page &$page)
    {
        $linkRepo = $this->em->getRepository(Link::class);

        $url = $this->urlHelper->parse($url, $page->getUrl());

        if (!$linkRepo->findOneBy(['url' => $url])) {
            $link = new Link($url, $title, $relevance);
            $this->em->persist($link);
            $page->addLink($link);
        }
    }
}