<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:19 PM
 */

namespace DownloaderBundle\Services\Helpers;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Monolog\Logger;
use PHPExif\Adapter\Exiftool;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpKernel\KernelInterface as Kernel;
use Symfony\Component\DomCrawler\Image as DomCrawlerImage;
use DownloaderBundle\Services\Helpers\Keyword as KeywordHelper;

class Image
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Image constructor.
     *
     * @param Kernel $kernel
     * @param EntityManager $em
     * @param Logger $logger
     */
    public function __construct(Kernel $kernel, EntityManager $em, Logger $logger)
    {
        $this->kernel = $kernel;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Download an image
     *
     * @param $pageUrl
     * @param DomCrawlerImage $element
     * @throws \Exception
     */
    public function download($pageUrl, DomCrawlerImage $element)
    {
        $src = strtok($element->getUri(), '?');
        $alt = $element->getNode()->getAttribute('alt');

        if ($saveDir = $this->getDirectory($pageUrl)) {
            $extension = pathinfo($src, PATHINFO_EXTENSION);

            if (!in_array($extension, $this->getParameter('allowed_image_extensions'))) {
                throw new \Exception("[ImageHelper] Invalid image extension: {$extension}");
            }

            $filename = pathinfo($src, PATHINFO_FILENAME);
            $imageFilePath = $saveDir . $filename . "." . $extension;

            try {
                $client = new Client([
                    'timeout' => 60,
                    'allow_redirects' => false,
                    'verify' => $this->getParameter('http_verify_ssl')
                ]);

                $client->get($src, ['sink' => $imageFilePath]);

                if ($this->isValid($imageFilePath)) {
                    $metadata = $this->getMetadata($imageFilePath);
                    $this->saveImage($element->getNode(), $src, $alt, $metadata);
                }
            } catch (\Exception $e) {
                $this->saveLog("[ImageHelper] At line {$e->getLine()}: {$e->getMessage()}");
                unlink($imageFilePath);
                throw $e;
            }
        }
    }

    /**
     * Save image entity
     *
     * @param \DOMElement $element
     * @param $src
     * @param string $alt
     * @param array $metadata
     * @return $this
     * @throws \Exception
     */
    protected function saveImage(\DOMElement $element, $src, $alt = '', array $metadata)
    {
        // Avoid duplication of image
        if ($this->em->getRepository(\AppBundle\Entity\Image::class)->findOneBy(['src' => $src])) {
            return $this;
        }

        $image = new \AppBundle\Entity\Image($src, $alt);
        $image->filename = isset($metadata['System:FileName']) ? $metadata['System:FileName'] : (pathinfo($src, PATHINFO_FILENAME) . '.' . pathinfo($src, PATHINFO_EXTENSION));
        $image->path = isset($metadata['System:Directory']) ? substr($metadata['System:Directory'], strpos($metadata['System:Directory'], 'images')) : null;
        $image->width = isset($metadata['File:ImageWidth']) ? $metadata['File:ImageWidth'] : 0;
        $image->height = isset($metadata['File:ImageHeight']) ? $metadata['File:ImageHeight'] : 0;
        $image->latitude = isset($metadata['GPS:GPSLatitude']) ? (float) $metadata['GPS:GPSLatitude'] : null;
        $image->longitude = isset($metadata['GPS:GPSLongitude']) ? (float) $metadata['GPS:GPSLongitude'] : null;
        $image->type = isset($metadata['File:FileType']) ? $metadata['File:FileType'] : pathinfo($src, PATHINFO_EXTENSION);
        $image->isExifLocation = !empty($image->latitude) && !empty($image->longitude);
        $image->description = $this->extractImageDescription($element, $alt, $image->filename);
        $image->setMetadata($metadata);

        if ($image->isExifLocation) {
            $image->isLocationCorrect = true;
        }

        // Save to database
        $this->em->persist($image);
        $this->em->flush($image);

        return $this;
    }

    /**
     * Extract image description
     *
     * @param \DOMElement $element
     * @param $alt
     * @param $filename
     * @return string
     */
    protected function extractImageDescription(\DOMElement $element, $alt, $filename)
    {
        $description = $alt . " " . $this->sanitize($filename);
        $prev = $element->previousSibling;
        $next = $element->nextSibling;

        if (get_class($prev) === \DOMElement::class && $prev->tagName !== 'script') {
            $textContent = $prev->textContent;
            $description .= " " . trim(strip_tags($textContent));
        }

        if (get_class($next) === \DOMElement::class && $next->tagName !== 'script') {
            $textContent = $next->textContent;
            $description .= " " . trim(strip_tags($textContent));
        }

        return trim($description);
    }

    /**
     * Extract image location data
     *
     * @param $filename
     * @return array
     */
    protected function getMetadata($filename)
    {
        $toolPath = $this->getParameter('exif_tool_path');

        switch (strtoupper(substr(PHP_OS, 0, 3))) {
            case "WIN": // Windows
                $toolPath .= "exiftool.exe";
                break;
            case "LIN": // Linux
                $toolPath .= "exiftool";
                break;
            case "UNI": // Unix
                $toolPath .= "exiftool";
                break;
            case "DAR": // MacOS
                $toolPath .= "exiftool.dmg";
                break;
        }

        $adapter = new Exiftool(
            array(
                'toolPath' => $toolPath,
            )
        );

        $reader = new Reader($adapter);
        $exif = $reader->read($filename);

        return array_merge($exif->getRawData(), $exif->getData());
    }

    /**
     * Check whether the image is valid
     *
     * @param $imageFilePath
     * @return bool
     */
    protected function isValid($imageFilePath)
    {
        list($width, $height) = @getimagesize($imageFilePath);

        if (!$width || !$height) {
            unlink($imageFilePath);
            $this->saveLog("[ImageHelper] Invalid aspect ratio for image {$imageFilePath}. The image dimension is {$width} x {$height}.");
            return false;
        }

        // Only proceed further if image size is larger than 400px in each dimension
        if ($width < $this->getParameter('image_min_width') && $height < $this->getParameter('image_min_height')) {
            unlink($imageFilePath);
            $this->saveLog("[ImageHelper] Invalid aspect ratio for image {$imageFilePath}. The image dimension is {$width} x {$height}.");
            return false;
        }

        return true;
    }

    /**
     * Get directory path for saving
     *
     * @param string $pageUrl
     * @return string
     */
    protected function getDirectory($pageUrl)
    {
        $imageDirectory = $this->getParameter('image_directory');

        $pageUrl = parse_url($pageUrl, PHP_URL_HOST);

        if (!$pageUrl) {
            return $imageDirectory;
        }

        $savePath = $imageDirectory . $pageUrl . "/";

        if (!file_exists($savePath)) {
            $canMkdir = @mkdir($savePath);
            if (!$canMkdir) {
                return $imageDirectory;
            }
        }

        return $savePath;
    }

    /**
     * Get application parameter
     *
     * @param $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->kernel->getContainer()->getParameter($name);
    }

    /**
     * Sanitize the file name to get words
     * for image description
     *
     * @param $filename
     * @return mixed
     */
    protected function sanitize($filename)
    {
        return preg_replace('/\W|(\bjpeg\b)|(\bpng\b)|(\bjpg\b)|(\bsvg\b)|\_/i', ' ', $filename);
    }

    /**
     * Log error messages and refresh/reopen entity manager
     *
     * @param $message
     */
    protected function saveLog($message)
    {
        $this->logger->debug($message);
    }

    /**
     * Get container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }
}