<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 25-Oct-17
 * Time: 22:22
 */

namespace AppBundle\Traits;

use AppBundle\Entity\Page;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait Downloader
{
    public function fetch(Page $page)
    {
        if (!$page->getHtml()) {
            $client = new HttpClient();

            $resource = $client->request(Request::METHOD_GET, $page->getUrl());

            if ($resource->getStatusCode() == Response::HTTP_OK) {
                $page->setHtml($resource->getBody());

                $dom = new \DOMDocument();
                @$dom->loadHTML($page->getHtml());

                $page->setDom($dom)
                    ->setTitle($dom->getElementsByTagName('title')->item(0)->textContent);

                $this->extractLinks($page);
            }
        }

    }

    protected function extractLinks($page)
    {

    }
}