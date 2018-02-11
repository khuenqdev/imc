<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:38 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Link;
use AppBundle\Entity\Seed;
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
     * @var Seed
     */
    private $seed;

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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $executionStartTime = microtime(true);
        // Set server memory limit before execution
        ini_set('memory_limit', $this->getContainer()->getParameter('server_memory_limit'));

        // Do styling for outputs
        $output->writeln('<comment>Initializing...</comment>');
        $style = new OutputFormatterStyle('red', null);
        $output->getFormatter()->setStyle('fail', $style);

        // Initialize the crawler
        $this->initializeCrawler();

        // Number of crawl pages
        $noOfPages = 0;

        // Main crawler loop
        while (!$this->queue->isEmpty() && $noOfPages < $this->getContainer()->getParameter('crawling_task_limit')) {

            $link = $this->queue->getNextLink();
            $output->write('Fetch: ' . $link->url);

            if($this->em->getRepository(Link::class)->isLinkVisited($link)) {
                $output->writeln(' <comment>SKIP</comment>');
                continue;
            }

            try {
                // Download content from the link
                $this->downloader->download($link->url);

                // Mark the link as visited
                $link->visited = true;
                $this->updateLink($link);

                // Output success message
                $output->writeln(' <info>SUCCESS</info>');
            } catch (\Exception $e) {
                $output->writeln(' <fail>FAILED</fail>');
            }

            // If there exists any output messages from the downloader, show it to the console
            $output->writeln($this->downloader->outputMessages);

            $noOfPages++;

            if ($noOfPages % 100 === 0) {
                $output->writeln("<fg=magenta;options=bold>[Memory Usage] " . $this->memoryUsage(true) . "</>");
            }

            //$output->writeln('Number of links in queue: ' . $this->queue->getSize());
        }

        // If there aren't any unvisited link left for the current seed, mark it as done and update to the database
        if (!$this->em->getRepository(Link::class)->getLastUnvisitedLink()) {
            $this->seed->isDone = true;
            $this->em->persist($this->seed);
            $this->em->flush($this->seed);
            $output->writeln("<info>All children pages of seed link {$this->seed->url} have been crawled!</info>");
        }

        $output->writeln('<comment>Crawling task finished!</comment>');
        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        $output->writeln("<comment>Total execution time: $seconds seconds</comment>");
    }

    /**
     * Get memory usage
     * @param bool $realUsage
     * @return string
     */
    private function memoryUsage($realUsage = false)
    {
        $size = memory_get_usage($realUsage);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * Initialize the crawler
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function initializeCrawler()
    {
        // Initialize services
        $this->queue = $this->getContainer()->get('queue');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get an undone seed
        $this->seed = $this->em->getRepository(Seed::class)->findOneBy(['isDone' => false]);

        // Get last unvisited link with highest relevance score as start link for the crawler
        /** @var Link $startLink */
        $startLink = $this->em->getRepository(Link::class)->getLastUnvisitedLink();

        // If no such link exists
        if (!$startLink) {
            // Use the seed instead
            if ($this->seed) {
                // Convert the seed to start link
                $startLink = $this->convertSeedToLink($this->seed);
            }
        }

        // Check once more before add start link to queue
        if ($startLink) {
            $this->queue->addLink($startLink);
        }
    }

    /**
     * Convert a seed to a link
     *
     * @param Seed $seed
     * @return Link
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function convertSeedToLink(Seed $seed)
    {
        $link = new Link($seed->url, $seed->title);

        return $this->updateLink($link);
    }

    /**
     * Update link to the database
     *
     * @param $link
     * @return Link
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateLink(Link $link)
    {
        $this->em->persist($link);
        $this->em->flush($link);
        $this->em->refresh($link);

        return $link;
    }
}