<?php

namespace AppBundle\Entity;

/**
 * Country
 *
 * GeoNames.org Country Information
 * ================================
 *
 * CountryCodes:
 * ============
 *
 * The official ISO country code for the United Kingdom is 'GB'. The code 'UK' is reserved.
 *
 * A list of dependent countries is available here:
 * https://spreadsheets.google.com/ccc?key=pJpyPy-J5JSNhe7F_KxwiCA&hl=en
 *
 * The countrycode XK temporarily stands for Kosvo:
 * http://geonames.wordpress.com/2010/03/08/xk-country-code-for-kosovo/
 *
 * CS (Serbia and Montenegro) with geonameId = 8505033 no longer exists.
 * AN (the Netherlands Antilles) with geonameId = 8505032 was dissolved on 10 October 2010.
 *
 * Currencies :
 * ============
 *
 * A number of territories are not included in ISO 4217, because their currencies are not per se an independent currency,
 * but a variant of another currency. These currencies are:
 *
 * 1. FO : Faroese krona (1:1 pegged to the Danish krone)
 * 2. GG : Guernsey pound (1:1 pegged to the pound sterling)
 * 3. JE : Jersey pound (1:1 pegged to the pound sterling)
 * 4. IM : Isle of Man pound (1:1 pegged to the pound sterling)
 * 5. TV : Tuvaluan dollar (1:1 pegged to the Australian dollar).
 * 6. CK : Cook Islands dollar (1:1 pegged to the New Zealand dollar).
 *
 * The following non-ISO codes are, however, sometimes used: GGP for the Guernsey pound,
 * JEP for the Jersey pound and IMP for the Isle of Man pound (http://en.wikipedia.org/wiki/ISO_4217)
 *
 *
 * A list of currency symbols is available here : http://forum.geonames.org/gforum/posts/list/437.page
 * another list with fractional units is here: http://forum.geonames.org/gforum/posts/list/1961.page
 *
 *
 * Languages :
 * ===========
 *
 * The column 'languages' lists the languages spoken in a country ordered by the number of speakers. The language code is a 'locale'
 * where any two-letter primary-tag is an ISO-639 language abbreviation and any two-letter initial subtag is an ISO-3166 country code.
 *
 * Example : es-AR is the Spanish variant spoken in Argentina.
 *
 * ISO    ISO3    ISO-Numeric    fips    Country    Capital    Area(in sq km)    Population    Continent    tld    CurrencyCode    CurrencyName    Phone    Postal Code Format    Postal Code Regex    Languages    geonameid    neighbours    EquivalentFipsCode
 *
 **/
class Country
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $iso;

    /**
     * @var string
     */
    private $iso3;

    /**
     * @var string
     */
    private $isoNumeric;

    /**
     * @var string
     */
    private $fips;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $capital;

    /**
     * @var int
     */
    private $area;

    /**
     * @var string
     */
    private $population;

    /**
     * @var string
     */
    private $continent;

    /**
     * @var string
     */
    private $tld;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $currencyName;

    /**
     * @var int
     */
    private $phone;

    /**
     * @var string
     */
    private $postalCodeFormat;

    /**
     * @var string
     */
    private $postalCodeRegex;

    /**
     * @var string
     */
    private $languages;

    /**
     * @var int
     */
    private $geonameid;

    /**
     * @var string
     */
    private $neighbours;

    /**
     * @var string
     */
    private $equivalentFipsCode;

    /**
     * Create from text data
     *
     * @param $data
     * @return Country
     */
    public static function createFromTxtData($data)
    {
        $country = new Country();
        
        $country->iso = $data[0];
        $country->iso3 = $data[1];
        $country->isoNumeric = $data[2];
        $country->fips = $data[3];
        $country->country = $data[4];
        $country->capital = $data[5];
        $country->area = $data[6];
        $country->population = $data[7];
        $country->continent = $data[8];
        $country->tld = $data[9];
        $country->currencyCode = $data[10];
        $country->currencyName = $data[11];
        $country->phone = $data[12];
        $country->postalCodeFormat = $data[13];
        $country->postalCodeRegex = $data[14];
        $country->languages = $data[15];
        $country->geonameid = (int)$data[16];
        $country->neighbours = $data[17];
        $country->equivalentFipsCode = $data[18];

        return $country;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set iso
     *
     * @param string $iso
     *
     * @return Country
     */
    public function setIso($iso)
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * Get iso
     *
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * Set iso3
     *
     * @param string $iso3
     *
     * @return Country
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    /**
     * Get iso3
     *
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * Set isoNumeric
     *
     * @param string $isoNumeric
     *
     * @return Country
     */
    public function setIsoNumeric($isoNumeric)
    {
        $this->isoNumeric = $isoNumeric;

        return $this;
    }

    /**
     * Get isoNumeric
     *
     * @return string
     */
    public function getIsoNumeric()
    {
        return $this->isoNumeric;
    }

    /**
     * Set fips
     *
     * @param string $fips
     *
     * @return Country
     */
    public function setFips($fips)
    {
        $this->fips = $fips;

        return $this;
    }

    /**
     * Get fips
     *
     * @return string
     */
    public function getFips()
    {
        return $this->fips;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Country
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set capital
     *
     * @param string $capital
     *
     * @return Country
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set area
     *
     * @param integer $area
     *
     * @return Country
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return int
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set population
     *
     * @param string $population
     *
     * @return Country
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return string
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set continent
     *
     * @param string $continent
     *
     * @return Country
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set tld
     *
     * @param string $tld
     *
     * @return Country
     */
    public function setTld($tld)
    {
        $this->tld = $tld;

        return $this;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }

    /**
     * Set currencyCode
     *
     * @param string $currencyCode
     *
     * @return Country
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * Get currencyCode
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set currencyName
     *
     * @param string $currencyName
     *
     * @return Country
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;

        return $this;
    }

    /**
     * Get currencyName
     *
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * Set phone
     *
     * @param integer $phone
     *
     * @return Country
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return int
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set postalCodeFormat
     *
     * @param string $postalCodeFormat
     *
     * @return Country
     */
    public function setPostalCodeFormat($postalCodeFormat)
    {
        $this->postalCodeFormat = $postalCodeFormat;

        return $this;
    }

    /**
     * Get postalCodeFormat
     *
     * @return string
     */
    public function getPostalCodeFormat()
    {
        return $this->postalCodeFormat;
    }

    /**
     * Set postalCodeRegex
     *
     * @param string $postalCodeRegex
     *
     * @return Country
     */
    public function setPostalCodeRegex($postalCodeRegex)
    {
        $this->postalCodeRegex = $postalCodeRegex;

        return $this;
    }

    /**
     * Get postalCodeRegex
     *
     * @return string
     */
    public function getPostalCodeRegex()
    {
        return $this->postalCodeRegex;
    }

    /**
     * Set languages
     *
     * @param string $languages
     *
     * @return Country
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages
     *
     * @return string
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set geonameid
     *
     * @param integer geonameid
     *
     * @return Country
     */
    public function setGeonameid($geonameid)
    {
        $this->geonameid = $geonameid;

        return $this;
    }

    /**
     * Get geonameid
     *
     * @return int
     */
    public function getGeonameid()
    {
        return $this->geonameid;
    }

    /**
     * Set neighbours
     *
     * @param string $neightbours
     *
     * @return Country
     */
    public function setNeighbours($neighbours)
    {
        $this->neightbours = $neighbours;

        return $this;
    }

    /**
     * Get neighbours
     *
     * @return string
     */
    public function getNeighbours()
    {
        return $this->neighbours;
    }

    /**
     * Set equivalentFipsCode
     *
     * @param string $equivalentFipsCode
     *
     * @return Country
     */
    public function setEquivalentFipsCode($equivalentFipsCode)
    {
        $this->equivalentFipsCode = $equivalentFipsCode;

        return $this;
    }

    /**
     * Get equivalentFipsCode
     *
     * @return string
     */
    public function getEquivalentFipsCode()
    {
        return $this->equivalentFipsCode;
    }
}

