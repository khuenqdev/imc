<?php


namespace GuiBundle\Services;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
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

    /**
     * Statistics constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get link and image statistics
     *
     * @return array
     */
    public function getStatistics(array $filters = [])
    {
        $linkRepo = $this->em->getRepository(Link::class);
        $imageRepo = $this->em->getRepository(Image::class);

        // If no filter specified, get all statistics
        if (empty($filters)) {
            $filters = [
                'general' => 1,
                'geoparsing' => 1,
                'regional' => 1,
                'address' => 1,
                'domain' => 1
            ];
        }

        $statistics = [];

        if (isset($filters['general']) && $filters['general']) {
            $statistics['no_of_links'] = $linkRepo->getNumberOfLinks();
            $statistics['visited_links'] = $linkRepo->getNumberOfVisitedLinks();
            $statistics['discovered_images'] = $imageRepo->getNumberOfImages();
            $statistics['images_with_exif_location'] = $imageRepo->getNoOfImagesWithExifLocation();
            $statistics['images_without_exif_location'] = $imageRepo->getNoOfImagesWithoutExifLocation();
            $statistics['images_with_gps_sensor_errors'] = $imageRepo->getNoOfImagesWithWrongGPSCoordinates();
            $statistics['average_image_size'] = $imageRepo->getAverageImageSize();
        }

        if (isset($filters['geoparsing']) && $filters['geoparsing']) {
            $statistics['successful_geoparsed_images'] = $imageRepo->getNoOfSuccessfulGeoparsedImages();
            $statistics['unsuccessful_geoparsed_images'] = $imageRepo->getNoOfUnsuccessfulGeoparsedImages();
            $statistics['correct_location_images'] = $imageRepo->getNoOfCorrectLocationImages();
            $statistics['incorrect_location_images'] = $imageRepo->getNoOfIncorrectLocationImages();
            $statistics['unverified_location_images'] = $imageRepo->getNoOfUnverifiedLocationImages();
        }

        if (isset($filters['regional']) && $filters['regional']) {
            $statistics['africa_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_AFRICA);
            $statistics['antarctic_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ANTARCTIC);
            $statistics['asia_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ASIA);
            $statistics['oceania_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_OCEANIA);
            $statistics['europe_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_EUROPE);
            $statistics['na_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_NA);
            $statistics['sa_images'] = $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_SA);
        }

        if (isset($filters['address']) && $filters['address']) {
            $addressImages = $imageRepo->getNumberOfImagesByAddresses();
            $noOfAddressImages = count($addressImages);

            $addressImagesInMetadata = array_filter($addressImages, function($var) {
                return ($var['is_exif_location'] === true);
            });
            $noOfAddressImagesInMetadata = count($addressImagesInMetadata);

            $statistics['address_images'] = $addressImages;
            $statistics['no_of_address_images'] = $noOfAddressImages;
            $statistics['no_of_address_images_in_metadata'] = $noOfAddressImagesInMetadata;
            $statistics['no_of_address_images_nin_metadata'] = $noOfAddressImages - $noOfAddressImagesInMetadata;
        }

        if (isset($filters['domain']) && $filters['domain']) {
            $statistics['domain_images'] = $imageRepo->getNumberOfImagesByDomain();
        }

        return $statistics;
    }
}