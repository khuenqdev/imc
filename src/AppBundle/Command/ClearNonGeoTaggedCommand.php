<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 6/10/2018
 * Time: 9:15 AM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Image;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearNonGeoTaggedCommand extends ContainerAwareCommand
{
    const LIMIT = 100;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('app:remove:nongeotagged')
            ->setDescription('Remove non-geotagged images from database and physical storage')
            ->setHelp('Remove non-geotagged images from database and physical storage');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $kernel = $this->getContainer()->get('kernel');
        $repo = $em->getRepository(Image::class);
        $total = $repo->getNoOfImagesWithoutExifLocation();
        $offset = 0;

        while ($total > 0) {
            $nonGeotagged = $repo->findImages([
                'only_non_geotagged' => 1,
                'offset' => 0,
                'limit' => self::LIMIT
            ]);

            $lowerBound = $offset + 1;
            $upperBound = $offset + self::LIMIT;
            $output->write("Processing image {$lowerBound} to {$upperBound} ");

            /** @var Image $image */
            foreach ($nonGeotagged as $image) {
                $metadata = $image->getImageMetadata();
                $storagePath = $metadata['SourceFile'];
                $thumbnailPath = $kernel->getContainer()
                        ->getParameter('image_thumbnail_directory') . $image->thumbnail;

                try {
                    unlink($storagePath);
                    unlink($thumbnailPath);
                } catch (\Exception $e) {
                }

                $em->remove($image);
                $em->flush();
            }

            $offset += self::LIMIT;
            $total = $repo->getNoOfImagesWithoutExifLocation();
            $output->writeln("<info>DONE</info>");
        }
    }
}