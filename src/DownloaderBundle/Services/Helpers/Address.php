<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 1/14/2018
 * Time: 12:04 PM
 */

namespace DownloaderBundle\Services\Helpers;

use AppBundle\Entity\Geoname;
use AppBundle\Repository\GeonameRepository;
use Doctrine\ORM\EntityManager;
use DownloaderBundle\Services\Helpers\String as StringHelper;
use DownloaderBundle\Services\Helpers\Keyword as KeywordHelper;

class Address
{
    public $em;
    public $string;
    public $keyword;

    public function __construct(EntityManager $em, StringHelper $string, KeywordHelper $keyword)
    {
        $this->em = $em;
        $this->string = $string;
        $this->keyword = $keyword;
    }

    public function convertToAddressString($text)
    {
        $addressComponents = [];

        // 1. Extract keywords from the text
        $tokenizedKeywords = $this->keyword->extract($text);

        // 2. Search the text for city
        $addressComponents['city'] = $this->getCity($tokenizedKeywords);

        return $addressComponents;
    }

    /**
     * @param $keywordTokens
     * @return array
     */
    protected function getCity(&$keywordTokens)
    {
        /** @var GeonameRepository $geonameRepo */
        $geonameRepo = $this->em->getRepository(Geoname::class);

        $cityCandidates = [];

        // Check if any of the token is a city
        foreach ($keywordTokens as $idx => $token) {
            if ($geonameRepo->isCityName($token)) {
                // Remove city's token from the keyword token list
                unset($keywordTokens[$idx]);

                // Then return turn the token as city component
                $cityCandidates[] = $token;
            }
        }

        return $cityCandidates;
    }
}