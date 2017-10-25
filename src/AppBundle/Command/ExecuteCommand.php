<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:45
 */

namespace AppBundle\Command;

use AppBundle\Services\CrawlerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends ContainerAwareCommand
{
    /**
     * @var CrawlerService
     */
    private $crawler;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('crawler:execute')
            ->setDescription('Execute the crawler')
            ->setHelp('Execute the crawler');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->crawler->crawl();
    }
}