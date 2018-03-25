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

    const BOUNDING_BOX_AUSTRALIA = [
        'min_lat' => -53.0587,
        'max_lat' => -6.0694,
        'min_lng' => 105.3770,
        'max_lng' => -175.2925
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
    public function getStatistics()
    {
        $linkRepo = $this->em->getRepository(Link::class);
        $imageRepo = $this->em->getRepository(Image::class);

        return [
            'no_of_links' => $linkRepo->getNumberOfLinks(),
            'visited_links' => $linkRepo->getNumberOfVisitedLinks(),
            'discovered_images' => $imageRepo->getNumberOfImages(),
            'images_with_exif_location' => $imageRepo->getNoOfImagesWithExifLocation(),
            'images_without_exif_location' => $imageRepo->getNoOfImagesWithoutExifLocation(),
            'successful_geoparsed_images' => $imageRepo->getNoOfSuccessfulGeoparsedImages(),
            'unsuccessful_geoparsed_images' => $imageRepo->getNoOfUnsuccessfulGeoparsedImages(),
            'correct_location_images' => $imageRepo->getNoOfCorrectLocationImages(),
            'incorrect_location_images' => $imageRepo->getNoOfIncorrectLocationImages(),
            'unverified_location_images' => $imageRepo->getNoOfUnverifiedLocationImages(),
            'africa_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_AFRICA),
            'antarctic_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ANTARCTIC),
            'asia_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_ASIA),
            'australia_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_AUSTRALIA),
            'europe_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_EUROPE),
            'na_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_NA),
            'sa_images' => $imageRepo->getNoOfImageInRegion(self::BOUNDING_BOX_SA),
        ];
    }
}