<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 4/9/2018
 * Time: 4:25 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Image;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeocodeCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('crawler:geocode')
            ->setDescription('Perform geocoding for images')
            ->setHelp('Perform a geocoding task');
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

        // Get all images that have not been geoparsed and location from EXIF
        $images = $em->getRepository(Image::class)->findBy([
            'isExifLocation' => true,
            'geoparsed' => false
        ]);

        $maxRetries = $this->getContainer()->getParameter('maximum_geoparser_retry');

        /** @var Image $image */
        foreach ($images as $image) {
            try {
                if (!$image->latitude && !$image->longitude) {
                    continue;
                }

                $output->writeln("Geocoding {$image->path}/{$image->filename}");
                $this->geocode($image);
                $image->geoparsed = true;
                $em->persist($image);
                $em->flush($image);
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");

                if ($image->geoparserRetries < $maxRetries) {
                    $image->geoparserRetries += 1;
                    $image->geoparsed = false;
                } else {
                    $image->geoparsed = true;
                }

                $em->persist($image);
                $em->flush($image);
            }
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