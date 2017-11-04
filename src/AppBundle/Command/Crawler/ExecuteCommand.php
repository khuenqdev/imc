<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:45
 */

namespace AppBundle\Command\Crawler;

use AppBundle\Components\Crawler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('crawler:execute')
            ->setDescription('Execute the crawler')
            ->setHelp('Execute the crawler');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('cpn.crawler')
            ->setOutputToCommandLine(true)
            ->crawl();
    }
}