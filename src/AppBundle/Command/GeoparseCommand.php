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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeoparseCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('crawler:geoparse')
            ->setDescription('Perform geoparsing for images')
            ->setHelp('Perform a geoparsing task')
            ->addOption('geocode_only', null, InputOption::VALUE_OPTIONAL, 'Perform only geocoding');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set server memory limit before execution
        ini_set('memory_limit', $this->getContainer()->getParameter('server_memory_limit'));

        // Get entity manager
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get all images that have not been geoparsed
        $images = $em->getRepository(Image::class)->findBy(['geoparsed' => false]);

        $maxRetries = $this->getContainer()->getParameter('maximum_geoparser_retry');

        /** @var Image $image */
        foreach ($images as $image) {
            try {

                if ($input->getOption('geocode_only')) {
                    $output->writeln("Geocoding {$image->path}/{$image->filename}");

                    if ($image->latitude && $image->longitude) {
                        $this->geocode($image);
                    }
                } else {
                    $output->writeln("Geoparsing {$image->path}/{$image->filename}");

                    if ($image->latitude && $image->longitude) {
                        $this->geocode($image);
                    } else {
                        $this->geoparse($image);
                        $image->isExifLocation = false;
                    }
                }

                $image->geoparsed = true;
                $em->persist($image);
                $em->flush($image);
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");

                if ($image->geoparserRetries < $maxRetries) {
                    $image->geoparserRetries += 1;
                } else {
                    $image->geoparsed = true;
                    $image->isLocationCorrect = false;
                }

                $em->persist($image);
                $em->flush($image);
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
            // Try the primary geo parser first
            $this->geoparsePrimary($client, $image);
        } catch (\Exception $e) {
            // Try the secondary also whenever there's an exception with the first
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
            'form_params' => [
                'inputText' => $image->description
            ],
            'headers' => ['Authorization' => 'apiKey ' . $this->getContainer()->getParameter('geoparser_api_key')]
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj && is_array($resultObj->features) && !empty($resultObj->features)) {
            $feature = reset($resultObj->features);
            $image->address = $feature->properties->name . ',' . $feature->properties->country;
            $image->longitude = $feature->geometry->coordinates[0];
            $image->latitude = $feature->geometry->coordinates[1];
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
                'moreinfo' => 1,
                'json' => 1,
                'auth' => $this->getContainer()->getParameter('secondary_geoparser_api_key')
            ],
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj && property_exists(get_class($resultObj), 'matches')
            && $resultObj->matches !== null) {
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