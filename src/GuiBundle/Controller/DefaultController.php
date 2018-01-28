<?php

namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $numberOfLinks = $this->getManager()
            ->getRepository(Link::class)
            ->getNumberOfLinks();
        $visitedLinks = $this->getManager()
            ->getRepository(Link::class)
            ->getNumberOfVisitedLinks();
        $discoveredImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNumberOfImages();
        $imagesWithExifLocation = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfImagesWithExifLocation();
        $imagesWithoutExifLocation = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfImagesWithoutExifLocation();
        $successfulGeoparsedImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfSuccessGeoparsedImages();
        $unsuccessfulGeoparsedImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfUnsuccessGeoparsedImages();
        $correctLocationImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfCorrectLocationImages();
        $incorrectLocationImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfIncorrectLocationImages();
        $unverifiedLocationImages = $this->getManager()
            ->getRepository(Image::class)
            ->getNoOfUnverifiedLocationImages();

        return $this->render('@Gui/gui/image_locations.html.twig', [
            'no_of_links' => $numberOfLinks,
            'visited_links' => $visitedLinks,
            'discovered_images' => $discoveredImages,
            'images_with_exif_location' => $imagesWithExifLocation,
            'images_without_exif_location' => $imagesWithoutExifLocation,
            'successful_geoparsed_images' => $successfulGeoparsedImages,
            'unsuccessful_geoparsed_images' => $unsuccessfulGeoparsedImages,
            'correct_location_images' => $correctLocationImages,
            'incorrect_location_images' => $incorrectLocationImages,
            'unverified_location_images' => $unverifiedLocationImages,
        ]);
    }

    /**
     * @return \Doctrine\ORM\EntityManager|object
     */
    public function getManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
