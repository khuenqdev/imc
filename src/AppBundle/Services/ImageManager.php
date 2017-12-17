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
     * ImageManager constructor.
     *
     * @param EntityManager $em
     * @param Kernel $kernel
     */
    public function __construct(EntityManager $em, Kernel $kernel, Logger $logger, Paginator $paginator)
    {
        $this->em = $em;
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->paginator = $paginator;
    }

    /**
     * List all images
     *
     * @return Image[]|array
     */
    public function listImages()
    {
        $images = $this->em->getRepository(Image::class)->findAll();

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