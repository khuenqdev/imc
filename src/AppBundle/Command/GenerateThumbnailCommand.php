<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 4/6/2018
 * Time: 9:32 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Image;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateThumbnailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('crawler:generate_thumbnail')
            ->setDescription('Generate image thumbnail')
            ->setHelp('Generate image thumbnail');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set server memory limit before execution
        ini_set('memory_limit', $this->getContainer()->getParameter('server_memory_limit'));

        // Get entity manager
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get all images that have not been geoparsed
        $images = $em->getRepository(Image::class)->findAll();
        $helper = $this->getContainer()->get('helper.image');

        /** @var Image $image */
        foreach ($images as $image) {
            $src = $image->src;
            $output->writeln("Processing {$src}");
            $helper->generateThumbnail($image);
        }

        try {
            $em->flush();
        } catch (OptimisticLockException $e) {
            $output->writeln("Error: {$e}");
        }
    }
}