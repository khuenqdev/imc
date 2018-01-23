<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 1/19/2018
 * Time: 10:55 AM
 */

namespace AppBundle\Command;


use AppBundle\Entity\Image;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeoparseCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('crawler:geoparse')
            ->setDescription('Perform a geoparsing for images')
            ->setHelp('Perform a crawling task');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set server memory limit before execution
        ini_set('memory_limit', $this->getContainer()->getParameter('server_memory_limit'));

        // Get entity manager
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get all images that have not been geoparsed
        $images = $em->getRepository(Image::class)->findBy(['geoparsed' => false]);

        /** @var Image $image */
        foreach ($images as $image) {
            try {
                $output->writeln("Geoparsing {$image->path}/{$image->filename}");

                if ($image->latitude && $image->longitude) {
                    $this->geocode($image);
                } else {
                    $this->geoparse($image);
                    $image->isExifLocation = false;
                }

                $image->geoparsed = true;
                $em->persist($image);
                $em->flush($image);
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
                break; // Break as soon as there is error
            }
        }
    }

    /**
     * Perform geoparsing
     *
     * @param Image $image
     */
    private function geoparse(Image &$image)
    {
        // Initialize client
        $client = new Client([
            'timeout' => 60,
            'allow_redirects' => false,
            'verify' => $this->getContainer()->getParameter('http_verify_ssl')
        ]);
        
        try {
            $this->geoparsePrimary($client, $image);
        } catch (\Exception $e) {
            $this->geoparseSecondary($client, $image);
        }
    }

    /**
     * Request for primary geo parser (geoparser.io)
     *
     * @param Client $client
     * @param Image $image
     */
    private function geoparsePrimary(Client $client, Image &$image)
    {
        // Do geoparsing process with primary geoparser
        $response = $client->post($this->getContainer()->getParameter('geoparser_url'), [
            'body' => [
                'inputText' => $image->description
            ],
            'headers' => ['Authorization' => 'apiKey ' . $this->getContainer()->getParameter('geoparser_api_key')]
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj && is_array($resultObj->features) && !empty($resultObj->features)) {
            $feature = reset($resultObj->features);
            $image->address = $feature->properties->name . ',' . $feature->properties->country;
            $image->latitude = $feature->geometry->coordinates[0];
            $image->longitude = $feature->geometry->coordinates[1];
        }
    }

    /**
     * Request for secondary geo parser (geocode.xyz) in case the primary failed
     *
     * @param Client $client
     * @param Image $image
     */
    private function geoparseSecondary(Client $client, Image &$image)
    {
        // Do geoparsing process with secondary geoparser
        $response = $client->get($this->getContainer()->getParameter('secondary_geoparser_url'), [
            'query' => [
                'scantext' => $image->description,
                'json' => 1,
                'auth' => $this->getContainer()->getParameter('secondary_geoparser_api_key')
            ],
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj && $resultObj->matches !== null) {
            if (is_array($resultObj->match)) {
                $match = $resultObj->match[0];
            } else {
                $match = $resultObj->match;
            }

            $image->address = $match->location;
            $image->latitude = $resultObj->latt;
            $image->longitude = $resultObj->longt;
        }
    }

    /**
     * Perform geocoding process
     *
     * @param Image $image
     */
    protected function geocode(Image &$image)
    {
        $client = new Client([
            'timeout' => 60,
            'allow_redirects' => false,
            'verify' => $this->getContainer()->getParameter('http_verify_ssl')
        ]);

        $response = $client->get($this->getContainer()->getParameter('google_geocode_url'), [
            'query' => [
                'latlng' => "{$image->latitude},{$image->longitude}",
                'key' => $this->getContainer()->getParameter('google_map_api_key'),
                'result_type' => "street_address|postal_code|country"
            ]
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj->status === "OK" && is_array($resultObj->results) && !empty($resultObj->results)) {
            $image->address = $resultObj->results[0]->formatted_address;
        }
    }
}