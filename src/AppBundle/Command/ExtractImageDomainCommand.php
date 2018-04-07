<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 4/6/2018
 * Time: 8:25 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Image;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtractImageDomainCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('crawler:extract_domain')
            ->setDescription('Extract domain of image original src')
            ->setHelp('Extract domain of image original src');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set server memory limit before execution
        ini_set('memory_limit', $this->getContainer()->getParameter('server_memory_limit'));

        // Get entity manager
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get all images that have not been geoparsed
        $images = $em->getRepository(Image::class)->findAll();

        /** @var Image $image */
        foreach ($images as $image) {
            $src = $image->src;

            $output->writeln("Processing {$src}");

            $host = parse_url($src, PHP_URL_HOST);
            if ($host && !empty($host)) {
                $domain = substr($host, strrpos($host, '.') + 1);
                $image->domain = $domain;
                $em->persist($image);
            }
        }

        try {
            $em->flush();
        } catch (OptimisticLockException $e) {
            $output->writeln("Error: {$e}");
        }
    }
}