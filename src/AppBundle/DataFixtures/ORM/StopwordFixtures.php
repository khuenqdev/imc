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
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $csv = $this->container->get('kernel')->getRootDir() . '/data/stop_words.csv';
        $content = @file_get_contents($csv);
        $words = str_getcsv($content, "\n");
        $wordCollection = $this->collectUniqueWords($words);

        foreach ($wordCollection as $hash => $word) {
            $stopword = new Stopword($word);
            $stopword->setHash($hash);
            $manager->persist($stopword);
        }

        $manager->flush();
    }

    /**
     * @param $words
     * @return array
     */
    private function collectUniqueWords($words)
    {
        $wordCollection = [];

        foreach ($words as $word) {
            $hash = sha1($word);
            $wordCollection[$hash] = $word;
        }

        return $wordCollection;
    }

}