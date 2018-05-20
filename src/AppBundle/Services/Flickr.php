<?php

namespace AppBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Flickr
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get most recent photos uploaded to Flickr
     *
     * @param $extras
     * @param $perPage
     * @param $page
     * @return array
     */
    public function apiGetRecent($extras = "", $perPage = 10, $page = 1)
    {
        $results = $this->requestResults('flickr.photos.getRecent',
            ['extras' => $extras, 'per_page' => $perPage, 'page' => $page]);
        return $this->mapToPhotoUrls($results);
    }

    /**
     * Map XML results returned by Flickr API to photo URLs
     *
     * @param \DOMDocument $resultDom
     * @return array
     */
    private function mapToPhotoUrls(\DOMDocument $resultDom)
    {
        if (!$resultDom) {
            return [];
        }

        $photoElements = $resultDom->getElementsByTagName('photo');
        $photos = [];

        if ($photoElements->length > 0) {
            for ($i = 0; $i < $photoElements->length; $i++) {
                /** @var \DOMElement $photo */
                $photo = $photoElements->item($i);
                $farm = $photo->getAttribute('farm');
                $server = $photo->getAttribute('server');
                $id = $photo->getAttribute('id');
                $secret = $photo->getAttribute('secret');
                $photos[] = "https://farm{$farm}.staticflickr.com/{$server}/{$id}_{$secret}.jpg";
            }
        }

        return $photos;
    }

    /**
     * Request API results
     *
     * @param $method
     * @param array $parameters
     * @param string $requestMethod
     * @return \DOMDocument|null
     */
    private function requestResults($method, $parameters = [], $requestMethod = Request::METHOD_GET)
    {
        $client = new Client([
            'timeout' => 60,
            'allow_redirects' => false,
            'verify' => $this->container->getParameter('http_verify_ssl')
        ]);

        $parameters = array_merge($parameters, [
            'method' => $method,
            'api_key' => $this->getApiKey()
        ]);

        try {
            $response = $client->request($requestMethod, $this->getApiUrl() . '?' . http_build_query($parameters));
            $content = $response->getBody()->getContents();
            $dom = new \DOMDocument();
            $dom->loadXML($content);

            return $dom;
        } catch (GuzzleException $e) {
        }

        return null;
    }

    /**
     * Get API URL
     * @param $method
     * @param array $parameters
     * @return string
     */
    private function getApiUrl()
    {
        return $this->container->getParameter('flickr_api_url');
    }

    /**
     * Get Flickr API key
     *
     * @return mixed
     */
    private function getApiKey()
    {
        return $this->container->getParameter('flickr_key');
    }
}
