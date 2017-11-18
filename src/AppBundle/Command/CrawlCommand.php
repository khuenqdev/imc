<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:38 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends ContainerAwareCommand
{
    protected $downloader;
    protected $queue;

    protected function configure()
    {
        //$this->queue = $this->getContainer()->get('queue');
        //$this->downloader = $this->getContainer()->get('');

        $this->setName('crawler:crawl')
            ->setDescription('Perform a crawling task')
            ->setHelp('Perform a crawling task');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}