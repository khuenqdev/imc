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
                $this->geoparse($image);

                if ($image->latitude && $image->longitude) {
                    $this->determineImageAddress($image);
                }

                $em->persist($image);
                $em->flush($image);
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        }
    }

    /**
     * Perform geoparsing
     *
     * @param $image
     */
    private function geoparse(&$image)
    {
        $client = new Client([
            'timeout' => 3,
            'allow_redirects' => false,
            'verify' => $this->getContainer()->getParameter('http_verify_ssl')
        ]);

        $response = $client->get($this->getContainer()->getParameter('geoparser_url'), [
            'query' => [
                'scantext' => $image->description,
                'json' => 1
            ]
        ]);

        $results = $response->getBody()->getContents();
        $resultObj = @json_decode($results);

        if ($resultObj->matches !== null) {
            if (is_array($resultObj->match)) {
                $match = $resultObj->match[0];
            } else {
                $match = $resultObj->match;
            }

            $image->address = $match->location;
            $image->latitude = $resultObj->latt;
            $image->longitude = $resultObj->longt;
            $image->isExifLocation = false;
        }
    }

    /**
     * Determine image's address
     *
     * @param Image $image
     */
    protected function determineImageAddress(Image &$image)
    {
        $client = new Client([
            'timeout' => 3,
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