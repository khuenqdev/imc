<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 15-Nov-17
 * Time: 22:42
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\DataFixtures\AbstractDataFixture;
use AppBundle\Entity\Stopword;
use Doctrine\Common\Persistence\ObjectManager;

class StopwordFixtures extends AbstractDataFixture
{
    public function load(ObjectManager $manager)
    {
        $csv = $this->container->get('router')->getContext()->getBaseUrl() . '/data/stop_words.csv';
        echo $csv . "\n";
        $content = @file_get_contents($csv);
        $words = str_getcsv($content, "\n");

        foreach ($words as $word) {
            $stopword = new Stopword($word);
            $manager->persist($stopword);
        }

        $manager->flush();
    }

    /**
     * Performs the actual fixtures loading.
     *
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     *
     * @param ObjectManager $manager The object manager.
     */
    protected function doLoad(ObjectManager $manager)
    {
        // TODO: Implement doLoad() method.
    }

    /**
     * Returns the environments the fixtures may be loaded in.
     *
     * @return array The name of the environments.
     */
    protected function getEnvironments()
    {
        return ['prod', 'dev', 'test'];
    }
}