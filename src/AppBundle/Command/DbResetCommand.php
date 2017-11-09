<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/9/2017
 * Time: 4:26 PM
 */

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DbResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:dbreset')
            ->setDescription('Reset database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearQueryCache = $this->getApplication()->find('doctrine:cache:clear-query');
        $clearQueryCache->run(new ArrayInput(['command' => 'doctrine:database:clear-query']), $output);

        $clearResultCache = $this->getApplication()->find('doctrine:cache:clear-result');
        $clearResultCache->run(new ArrayInput(['command' => 'doctrine:database:clear-result']), $output);

        $clearMetaDataCache = $this->getApplication()->find('doctrine:cache:clear-metadata');
        $clearMetaDataCache->run(new ArrayInput(['command' => 'doctrine:database:clear-metadata']), $output);

        $databaseDrop = $this->getApplication()->find('doctrine:database:drop');
        $databaseDrop->run(new ArrayInput(['command' => 'doctrine:database:drop', '--force' => true]), $output);

        $databaseCreate = $this->getApplication()->find('doctrine:database:create');
        $databaseCreate->run(new ArrayInput(['command' => 'doctrine:database:create']), $output);

        $databaseUpdate = $this->getApplication()->find('doctrine:schema:update');
        $databaseUpdate->run(new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]), $output);

        $initialFixtures = $this->getApplication()->find('doctrine:fixtures:load');
        $initialFixtures->run(new ArrayInput(['command' => 'doctrine:fixtures:load']), $output);
    }
}