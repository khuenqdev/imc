<?php

namespace AppBundle\Entity;

/**
 * GeoName
 */
class GeoName
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
     * Create a new geo name data
     *
     * @param array $data
     * @return GeoName
     */
    public static function createFromTxtData(array $data)
    {
        $geoName = new GeoName();
        $geoName->id = (int)$data[0];
        $geoName->name = $data[1];
        $geoName->asciiName = $data[2];
        $geoName->alternateNames = $data[3];
        $geoName->latitude = (float)$data[4];
        $geoName->longitude = (float)$data[5];
        $geoName->featureClass = $data[6];
        $geoName->featureCode = $data[7];
        $geoName->countryCode = $data[8];
        $geoName->cc2 = $data[9];
        $geoName->admin1Code = $data[10];
        $geoName->admin2Code = $data[11];
        $geoName->admin3Code = $data[12];
        $geoName->admin4Code = $data[13];
        $geoName->population = (int)$data[14];
        $geoName->elevation = (int)$data[15];
        $geoName->dem = (int)$data[16];
        $geoName->timezone = $data[17];
        $geoName->modificationDate = new \DateTime($data[18]);

        return $geoName;
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
     * Set name
     *
     * @param string $name
     *
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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
     * @return GeoName
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

