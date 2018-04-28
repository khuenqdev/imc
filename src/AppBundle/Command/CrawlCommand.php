<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/17/2017
 * Time: 6:38 PM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Link;
use AppBundle\Entity\Report;
use AppBundle\Entity\Seed;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Downloader;
use QueueBundle\Queue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $limit = $this->getContainer()->getParameter('crawling_task_limit');

        // Main crawler loop
        while (!$this->queue->isEmpty() && $noOfPages < $limit) {

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

            // If the queue is empty but we have not reach the current crawling task's limit, reinitialize it
            if ($this->queue->isEmpty() && $noOfPages < $limit) {
                $this->initializeQueue();
            }

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

        // Add the number of visited links to report entity
        $this->downloader->getReport()->noOfVisitedLinks = $noOfPages;

        // Add execution time to the report
        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        $this->downloader->getReport()->executionTime = $seconds;
        $this->downloader->getReport()->endAt = new \DateTime();

        // Print out crawling task report and save report to database
        $this->printReport($input, $output, $this->downloader->getReport());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Report $report
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function printReport(InputInterface $input, OutputInterface $output, Report $report)
    {
        $reportStyle = new SymfonyStyle($input, $output);

        // Report finished crawling task statistics
        $reportStyle->title("CRAWLING TASK REPORT");
        $reportStyle->table([], [
            ["Start At", "{$report->startAt->format('Y-m-d H:i:s')}"],
            ["End At", "{$report->endAt->format('Y-m-d H:i:s')}"],
            ["Total execution time", "{$report->executionTime} seconds"],
            ["Discovered links", "{$report->noOfLinks}"],
            ["Visited links", "{$report->noOfVisitedLinks}"],
            ["Discovered images", "{$report->noOfImages}"],
            ["Geotagged Images", "{$report->noOfExifImages}"],
        ]);

        // Save report
        $output->writeln("<comment>Saving report...</comment>");
        $this->saveReport($report);
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
     * @throws \Doctrine\ORM\ORMException
     */
    private function initializeCrawler()
    {
        // Initialize services
        $this->queue = $this->getContainer()->get('queue');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->initializeQueue();
    }

    /**
     * Initialize the queue
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    private function initializeQueue()
    {
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

    /**
     * @param Report $report
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveReport(Report $report)
    {
        $this->em->persist($report);
        $this->em->flush($report);
        $this->em->refresh($report);
    }
}
