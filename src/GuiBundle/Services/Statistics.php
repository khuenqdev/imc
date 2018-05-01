<?php


namespace GuiBundle\Services;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use AppBundle\Entity\Report;
use Doctrine\ORM\EntityManager;

class Statistics
{

    const BOUNDING_BOX_AFRICA = [
        'min_lat' => -46.9005,
        'max_lat' => 37.5671,
        'min_lng' => -25.3587,
        'max_lng' => 63.5254
    ];

    const BOUNDING_BOX_ANTARCTIC = [
        'min_lat' => -90.0000,
        'max_lat' => -53.0077,
        'min_lng' => -180.0000,
        'max_lng' => 180.0000
    ];

    const BOUNDING_BOX_ASIA = [
        'min_lat' => -12.5611,
        'max_lat' => 82.5005,
        'min_lng' => 19.6381,
        'max_lng' => 180.0000
    ];

    const BOUNDING_BOX_OCEANIA = [
        'min_lat' => -53.215743,
        'max_lat' => -7.607290,
        'min_lng' => -145.298644,
        'max_lng' => 180.686459
    ];

    const BOUNDING_BOX_EUROPE = [
        'min_lat' => 27.6363,
        'max_lat' => 81.0088,
        'min_lng' => -31.2660,
        'max_lng' => 39.8693
    ];

    const BOUNDING_BOX_NA = [
        'min_lat' => 5.4995,
        'max_lat' => 83.1621,
        'min_lng' => -167.2764,
        'max_lng' => -52.2330
    ];

    const BOUNDING_BOX_SA = [
        'min_lat' => -59.4505,
        'max_lat' => 13.3903,
        'min_lng' => -109.4749,
        'max_lng' => -26.3325
    ];

    /**
     * @var EntityManager
     */
    private $em;

    private $linkRepo;
    private $imageRepo;
    private $reportRepo;

    /**
     * Statistics constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->linkRepo = $this->em->getRepository(Link::class);
        $this->imageRepo = $this->em->getRepository(Image::class);
        $this->reportRepo = $this->em->getRepository(Report::class);
    }

    /**
     * Get link and image statistics
     *
     * @param array $filters
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function getStatistics(array $filters = [])
    {
        // If no filter specified, get all statistics
        if (empty($filters)) {
            $filters = [
                'general' => 1,
                'geoparsing' => 1,
                'regional' => 1,
                'address' => 1,
                'domain' => 1,
                'execution_times' => 1
            ];
        }

        $statistics = [];

        if (isset($filters['general']) && $filters['general']) {
            $statistics = array_merge($statistics, $this->getGeneralStatistics());
        }

        if (isset($filters['geoparsing']) && $filters['geoparsing']) {
            $statistics = array_merge($statistics, $this->getGeoparsingStatistics());
        }

        if (isset($filters['regional']) && $filters['regional']) {
            $statistics = array_merge($statistics, $this->getRegionalStatistics());
        }

        if (isset($filters['address']) && $filters['address']) {
            $statistics = array_merge($statistics, $this->getAddressStatistics());
        }

        if (isset($filters['domain']) && $filters['domain']) {
            $statistics = array_merge($statistics, $this->getDomainStatistics());
        }

        if (isset($filters['execution_times']) && $filters['execution_times']) {
            $statistics = array_merge($statistics, $this->getExecutionTimeReportStatistics(100));
        }

        return $statistics;
    }

    /**
     * Get general statistics
     *
     * @return array
     */
    public function getGeneralStatistics()
    {
        $statistics = [];
        $statistics['no_of_links'] = $this->linkRepo->getNumberOfLinks();
        $statistics['visited_links'] = $this->linkRepo->getNumberOfVisitedLinks();
        $statistics['discovered_images'] = $this->imageRepo->getNumberOfImages();
        $statistics['images_with_exif_location'] = $this->imageRepo->getNoOfImagesWithExifLocation();
        $statistics['images_without_exif_location'] = $this->imageRepo->getNoOfImagesWithoutExifLocation();
        $statistics['images_with_gps_sensor_errors'] = $this->imageRepo->getNoOfImagesWithWrongGPSCoordinates();
        $statistics['average_image_size'] = $this->imageRepo->getAverageImageSize();

        return $statistics;
    }

    /**
     * Get regional statistics (inaccurate due to bounding box)
     *
     * @return array
     */
    public function getRegionalStatistics()
    {
        $statistics = [];
        $statistics['africa_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_AFRICA);
        $statistics['antarctic_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ANTARCTIC);
        $statistics['asia_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ASIA);
        $statistics['oceania_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_OCEANIA);
        $statistics['europe_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_EUROPE);
        $statistics['na_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_NA);
        $statistics['sa_images'] = $this->imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_SA);

        return $statistics;
    }

    /**
     * Get geoparsing statistics
     *
     * @return array
     */
    public function getGeoparsingStatistics()
    {
        $statistics = [];
        $statistics['successful_geoparsed_images'] = $this->imageRepo->getNoOfSuccessfulGeoparsedImages();
        $statistics['unsuccessful_geoparsed_images'] = $this->imageRepo->getNoOfUnsuccessfulGeoparsedImages();
        $statistics['correct_location_images'] = $this->imageRepo->getNoOfCorrectLocationImages();
        $statistics['incorrect_location_images'] = $this->imageRepo->getNoOfIncorrectLocationImages();
        $statistics['unverified_location_images'] = $this->imageRepo->getNoOfUnverifiedLocationImages();

        return $statistics;
    }

    /**
     * Get address statistics
     *
     * @return mixed
     */
    public function getAddressStatistics()
    {
        $addressImages = $this->imageRepo->getNumberOfImagesByAddresses();
        $noOfAddressImages = count($addressImages);

        $addressImagesInMetadata = array_filter($addressImages, function ($var) {
            return ($var['is_exif_location'] === true);
        });
        $noOfAddressImagesInMetadata = count($addressImagesInMetadata);

        $statistics['address_images'] = $addressImages;
        $statistics['no_of_address_images'] = $noOfAddressImages;
        $statistics['no_of_address_images_in_metadata'] = $noOfAddressImagesInMetadata;
        $statistics['no_of_address_images_nin_metadata'] = $noOfAddressImages - $noOfAddressImagesInMetadata;

        return $statistics;
    }

    /**
     * Get domain statistics
     *
     * @return array
     */
    public function getDomainStatistics()
    {
        $statistics = [];
        $statistics['domain_images'] = $this->imageRepo->getNumberOfImagesByDomain();

        return $statistics;
    }

    /**
     * Get execution time report of the last 100 crawling tasks
     * plus the average execution times of all crawling task
     *
     * @param int $limit
     * @return array
     */
    public function getExecutionTimeReportStatistics($limit = 100)
    {
        $executionTimes = $this->reportRepo->getReport($limit);

        uasort($executionTimes, function ($a, $b) {
            if ($a['id'] == $b['id']) {
                return 0;
            }

            return ($a['id'] < $b['id']) ? -1 : 1;
        });

        $averageExecutionTime = 0;

        foreach ($executionTimes as $idx => $entry) {
            $averageExecutionTime += $entry['executionTime'];
            /** @var \DateTime $startAt */
            $startAt = $executionTimes[$idx]['startAt'];
            $executionTimes[$idx]['startAt'] = $startAt->format('d/m/Y');
        }

        if ($noOfExecTimes = count($executionTimes)) {
            $averageExecutionTime /= $noOfExecTimes;
        }

        return [
            'average_execution_time' => $averageExecutionTime,
            'execution_times' => $executionTimes
        ];
    }

    /**
     * Get crawling task execution full reports
     *
     * @return array
     */
    public function getReports()
    {
        return $this->reportRepo->getReport();
    }
}
