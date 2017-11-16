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
        $csv = $this->container->get('kernel')->getRootDir() . '/data/stop_words.csv';
        $content = @file_get_contents($csv);
        $words = str_getcsv($content, "\n");

        foreach ($words as $word) {
            $hash = sha1($word);

            if (!$manager->getRepository(Stopword::class)->findOneBy(['hash' => $hash])) {
                $stopword = new Stopword($word);
                $stopword->setHash($hash);

                $manager->persist($stopword);
                $manager->flush();
            }
        }
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