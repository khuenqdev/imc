<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 25-Oct-17
 * Time: 22:22
 */

namespace AppBundle\Components;

use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\Helpers\String as StringHelper;
use AppBundle\Services\Helpers\Url as UrlHelper;

class Downloader
{
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

    public function __construct(EntityManager $em, StringHelper $stringHelper, UrlHelper $urlHelper)
    {

    }

    /**
     * Retrieve a page's data
     *
     * @param Page $page
     * @throws \Exception
     */
    public function retrieve(Page $page)
    {
        if (!$page->getHtml()) {

            if (!$page->getUrl()) {
                throw new \Exception("Unable to fetch page: Page URL reference must not be empty!");
            }

            $client = new HttpClient();

            $resource = $client->request(Request::METHOD_GET, $page->getUrl());

            if ($resource->getStatusCode() == Response::HTTP_OK) {
                $page->setHtml($resource->getBody());

                $dom = new \DOMDocument();
                @$dom->loadHTML($page->getHtml());

                $page->setDom($dom)
                    ->setTitle($dom->getElementsByTagName('title')->item(0)->textContent);

                $this->extractLinks($dom);
            }
        }

    }

    protected function extractLinks(\DOMDocument $dom)
    {
        $linkElements = $dom->getElementsByTagName('a');

        $links = array();

        /** @var \DOMElement $linkElement */
        foreach ($linkElements as $linkElement) {

            // Get the Hypertext Reference attribute of the link
            $href = $linkElement->getAttribute('href');

            // Parse the reference to obtain URL reference
            $url = $this->urlHelper->parse($href);
        }
    }
}