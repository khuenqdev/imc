<?php


namespace GuiBundle\Services;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;

class Statistics
{
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
            'unverified_location_images' => $imageRepo->getNoOfUnverifiedLocationImages()
        ];
    }
}