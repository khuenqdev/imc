<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 4/9/2018
 * Time: 5:23 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Image;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectLocationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('crawler:correct:location')
            ->setDescription('Correct image GPS location')
            ->setHelp('Correct image GPS location');
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
            'isExifLocation' => true
        ]);

        /** @var Image $image */
        foreach ($images as $image) {

            $output->writeln("Processing {$image->path}/{$image->filename}");
            $metadata = $image->getMetadata();

            if (isset($metadata['GPS:GPSLatitudeRef'])) {
                $image->latitudeRef = $metadata['GPS:GPSLatitudeRef'];
            }

            if (isset($metadata['GPS:GPSLongitudeRef'])) {
                $image->longitudeRef = $metadata['GPS:GPSLongitudeRef'];
            }

            $em->persist($image);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}