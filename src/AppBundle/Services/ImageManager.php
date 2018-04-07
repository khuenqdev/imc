<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/16/2017
 * Time: 1:06 PM
 */

namespace AppBundle\Services;


use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface as Kernel;

class ImageManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ImageManager constructor.
     *
     * @param EntityManager $em
     * @param Kernel $kernel
     */
    public function __construct(
        EntityManager $em,
        Kernel $kernel,
        Logger $logger,
        Paginator $paginator,
        Session $session,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->session = $session;
        $this->requestStack = $requestStack;
    }

    /**
     * List all images
     *
     * @param array $filters
     * @return Image[]|array
     */
    public function listImages(array $filters = [])
    {
        $images = $this->em
            ->getRepository(Image::class)
            ->findImages($filters);

        return $images;
    }

    /**
     * Create a new image
     *
     * @deprecated
     * @param Link|null $source
     * @param array $payload
     * @throws \Exception
     */
    public function createImage($source, $payload = [])
    {
        $image = new Image($source, $payload['src']);
        $this->updateImage($image, $payload);
    }

    /**
     * Update image data
     *
     * @throws \Exception
     */
    public function updateImage(Image $image, $data)
    {
        foreach ($data as $name => $value) {
            if (property_exists(Image::class, $name)) {
                $image->$name = $value;
            }
        }

        try {
            $this->em->persist($image);
            $this->em->flush($image);
        } catch (\Exception $e) {
            $this->logger->debug("[ImageManager] {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Delete an image
     *
     * @throws \Exception
     */
    public function deleteImage(Image $image)
    {
        try {
            $imagePath = $this->kernel->getRootDir()
                . "/../web/downloaded/"
                . $this->getParameter('image_directory')
                . "/" . $image->path
                . "/" . $image->filename;

            $this->em->remove($image);
            $this->em->flush($image);

            // Delete physical file
            unlink($imagePath);
        } catch (\Exception $e) {
            $this->logger->debug("[ImageManager] {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Get an unverified image
     *
     * @return Image|null|object
     */
    public function fetchUnverifiedImage()
    {
        // Get an image that is geoparsed but has yet to be verified
        return $this->em->getRepository(Image::class)->findUnverifiedImage(
            $this->session->get('ignored_image_ids')
        );
    }

    /**
     * Verify location information of an image
     *
     * @param Image $image
     * @param $isCorrect
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function verifyImageLocation(Image $image, $isCorrect)
    {
        $image->isLocationCorrect = (bool)$isCorrect;
        $this->em->persist($image);
        $this->em->flush($image);
    }

    /**
     * Get all available marker locations
     *
     * @param array $filters
     * @return array
     */
    public function getMarkerLocations(array $filters = [])
    {
        $markers = $this->em->getRepository(Image::class)
            ->getLocationCoordinates($filters);

        $markers = array_map(function ($image) {
            $host = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
            $basePath = $this->requestStack->getCurrentRequest()->getBasePath();
            $image['thumbnail'] = $basePath . '/downloaded/thumbnails/' . $image['thumbnail'];
            $image['photourl'] = $host . $basePath . '/downloaded/' . $image['path'] . '/' . $image['filename'];
            unset($image['path']);
            unset($image['filename']);

            return $image;
        }, $markers);

        return $markers;
    }

    /**
     * Get link and image statistics
     *
     * @param array $filters
     * @return array
     */
    public function getStatistics(array $filters = [])
    {
        $service = $this->kernel->getContainer()->get('gui.statistics');

        return $service->getStatistics($filters);
    }

    /**
     * Get kernel parameter
     *
     * @param $name
     * @return mixed
     */
    private function getParameter($name)
    {
        return $this->kernel->getContainer()->getParameter($name);
    }
}