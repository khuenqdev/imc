<?php

namespace AppBundle\Entity;

/**
 * GeoName
 */
class Geoname
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $asciiName;

    /**
     * @var string
     */
    private $alternateNames;

    /**
     * @var string
     */
    private $latitude;

    /**
     * @var string
     */
    private $longitude;

    /**
     * @var string
     */
    private $featureClass;

    /**
     * @var string
     */
    private $featureCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $cc2;

    /**
     * @var string
     */
    private $admin1Code;

    /**
     * @var string
     */
    private $admin2Code;

    /**
     * @var string
     */
    private $admin3Code;

    /**
     * @var string
     */
    private $admin4Code;

    /**
     * @var int
     */
    private $population;

    /**
     * @var int
     */
    private $elevation;

    /**
     * @var int
     */
    private $dem;

    /**
     * @var string
     */
    private $timezone;

    /**
     * @var \DateTime
     */
    private $modificationDate;

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
     * Set name
     *
     * @param string $name
     *
     * @return Geoname
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set asciiName
     *
     * @param string $asciiName
     *
     * @return Geoname
     */
    public function setAsciiName($asciiName)
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    /**
     * Get asciiName
     *
     * @return string
     */
    public function getAsciiName()
    {
        return $this->asciiName;
    }

    /**
     * Set alternateNames
     *
     * @param string $alternateNames
     *
     * @return Geoname
     */
    public function setAlternateNames($alternateNames)
    {
        $this->alternateNames = $alternateNames;

        return $this;
    }

    /**
     * Get alternateNames
     *
     * @return string
     */
    public function getAlternateNames()
    {
        return $this->alternateNames;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Geoname
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Geoname
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set featureClass
     *
     * @param string $featureClass
     *
     * @return Geoname
     */
    public function setFeatureClass($featureClass)
    {
        $this->featureClass = $featureClass;

        return $this;
    }

    /**
     * Get featureClass
     *
     * @return string
     */
    public function getFeatureClass()
    {
        return $this->featureClass;
    }

    /**
     * Set featureCode
     *
     * @param string $featureCode
     *
     * @return Geoname
     */
    public function setFeatureCode($featureCode)
    {
        $this->featureCode = $featureCode;

        return $this;
    }

    /**
     * Get featureCode
     *
     * @return string
     */
    public function getFeatureCode()
    {
        return $this->featureCode;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return Geoname
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set cc2
     *
     * @param string $cc2
     *
     * @return Geoname
     */
    public function setCc2($cc2)
    {
        $this->cc2 = $cc2;

        return $this;
    }

    /**
     * Get cc2
     *
     * @return string
     */
    public function getCc2()
    {
        return $this->cc2;
    }

    /**
     * Set admin1Code
     *
     * @param string $admin1Code
     *
     * @return Geoname
     */
    public function setAdmin1Code($admin1Code)
    {
        $this->admin1Code = $admin1Code;

        return $this;
    }

    /**
     * Get admin1Code
     *
     * @return string
     */
    public function getAdmin1Code()
    {
        return $this->admin1Code;
    }

    /**
     * Set admin2Code
     *
     * @param string $admin2Code
     *
     * @return Geoname
     */
    public function setAdmin2Code($admin2Code)
    {
        $this->admin2Code = $admin2Code;

        return $this;
    }

    /**
     * Get admin2Code
     *
     * @return string
     */
    public function getAdmin2Code()
    {
        return $this->admin2Code;
    }

    /**
     * Set admin3Code
     *
     * @param string $admin3Code
     *
     * @return Geoname
     */
    public function setAdmin3Code($admin3Code)
    {
        $this->admin3Code = $admin3Code;

        return $this;
    }

    /**
     * Get admin3Code
     *
     * @return string
     */
    public function getAdmin3Code()
    {
        return $this->admin3Code;
    }

    /**
     * Set admin4Code
     *
     * @param string $admin4Code
     *
     * @return Geoname
     */
    public function setAdmin4Code($admin4Code)
    {
        $this->admin4Code = $admin4Code;

        return $this;
    }

    /**
     * Get admin4Code
     *
     * @return string
     */
    public function getAdmin4Code()
    {
        return $this->admin4Code;
    }

    /**
     * Set population
     *
     * @param integer $population
     *
     * @return Geoname
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return int
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set elevation
     *
     * @param integer $elevation
     *
     * @return Geoname
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return int
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set dem
     *
     * @param integer $dem
     *
     * @return Geoname
     */
    public function setDem($dem)
    {
        $this->dem = $dem;

        return $this;
    }

    /**
     * Get dem
     *
     * @return int
     */
    public function getDem()
    {
        return $this->dem;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     *
     * @return Geoname
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     *
     * @return Geoname
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}

