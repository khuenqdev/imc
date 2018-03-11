<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 12:57 AM
 */

namespace Tests\Downloader;

use AppBundle\Entity\Link;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Downloader;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class DownloaderTest extends KernelTestCase
{
    /**
     * @var Downloader
     */
    protected $service;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Set up the test
     */
    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('downloader');
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Test download a web page
     */
    public function testDownload()
    {
        echo "\n";
        $url = "http://www.bbc.com/travel/story/20170323-the-deadly-dish-people-love-to-eat";

        $client = new Client([
            'base_uri' => $url,
            'timeout' => 60,
            'allow_redirects' => true,
            'verify' => false
        ]);

        $response = $client->get($url);

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $dom = new Crawler($response->getBody()->getContents(), $url);
            $imageElements = $dom->filter('img')->images();
            $current = $imageElements[2]->getNode();

            $alt = trim($current->getAttribute('alt'));
            $title = trim($current->getAttribute('title'));
            $filename = $this->sanitize(pathinfo($current->getAttribute('src'), PATHINFO_FILENAME));

            $description = "Alt: {$alt} Title: {$title} Filename: {$filename}";

            echo $description . "\n";
        }
    }

    private function getNodeText($node) {
        $allowedTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'p', 'a', 'div', 'span'];

        if ($node instanceof \DOMElement
            && in_array($node->tagName, $allowedTags)
            && !empty($node->textContent)) {
            return str_replace('/\W/ig', " ", trim(strip_tags($node->textContent)));
        }

        return null;
    }

    private function sanitize($filename)
    {
        return preg_replace('/\W|(\bjpeg\b)|(\bpng\b)|(\bjpg\b)|(\bsvg\b)|\_/i', ' ', $filename);
    }
}