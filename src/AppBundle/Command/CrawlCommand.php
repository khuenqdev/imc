<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:38 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Downloader;
use QueueBundle\Queue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends ContainerAwareCommand
{
    const LIMIT = 5000;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('crawler:crawl')
            ->setDescription('Perform a crawling task')
            ->setHelp('Perform a crawling task');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Initialize the crawler
        $output->writeln('<comment>Initializing...</comment>');
        $style = new OutputFormatterStyle('red', null);
        $output->getFormatter()->setStyle('fail', $style);

        ini_set('memory_limit', '3G');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->queue = $this->getContainer()->get('queue');
        $this->downloader = $this->getContainer()->get('downloader');

        /** @var Link $seed */
        if ($seed = $this->em->getRepository(Link::class)->getSeedLink()) {
            $this->queue->addLink($seed);
        }

        $noOfPages = 0;

        while (!$this->queue->isEmpty() && $noOfPages < self::LIMIT) {

            $link = $this->queue->getNextLink();
            $output->write('Fetch: ' . $link->url);

            $downloadResults = $this->downloader->download($link);

            if ($downloadResults === true) {
                $this->markLinkAsVisited($link);
                $output->writeln(' <info>SUCCESS</info>');
                $noOfPages++;
            } else {
                $output->writeln(' <fail>FAILED</fail>');
                $output->writeln("<error>{$this->downloader->getErrorMessage()}</error>");
            }

            if ($noOfPages % 100 === 0) {
                $output->writeln("<fg=magenta;options=bold>[Memory Usage] " . $this->memoryUsage(true) . "</>");
            }
        }

        $output->writeln('<comment>Crawling task finished!</comment>');
    }

    /**
     * Mark a link as visited
     *
     * @param $link
     */
    protected function markLinkAsVisited($link)
    {
        $link->visited = true;
        $this->em->persist($link);
        $this->em->flush($link);
    }

    /**
     * Get memory usage
     * @param bool $realUsage
     * @return string
     */
    protected function memoryUsage($realUsage = false)
    {
        $size = memory_get_usage($realUsage);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}