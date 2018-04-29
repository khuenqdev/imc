<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:19 PM
 */

namespace DownloaderBundle\Services\Helpers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
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
     * @return \AppBundle\Entity\Image|null
     * @throws \Exception
     */
    public function download($pageUrl, DomCrawlerImage $element)
    {
        $image = null;

        $src = strtok($element->getUri(), '?');

        // Avoid duplication of image
        if ($this->em->getRepository(\AppBundle\Entity\Image::class)->findOneBy(['src' => $src])) {
            return $image;
        }

        if ($saveDir = $this->getDirectory($pageUrl)) {
            $extension = pathinfo($src, PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), $this->getParameter('allowed_image_extensions'))) {
                throw new \Exception("[ImageHelper] Invalid image extension: {$extension}");
            }

            $filename = pathinfo($src, PATHINFO_FILENAME);
            $imageFilePath = $saveDir . $filename . "." . $extension;

            try {
                $client = new Client([
                    'timeout' => 60,
                    'allow_redirects' => true,
                    'verify' => $this->getParameter('http_verify_ssl')
                ]);

                $client->get($src, ['sink' => $imageFilePath]);

                $metadata = $this->getMetadata($imageFilePath);
                $image = $this->saveImage($element->getNode(), $imageFilePath, $src, $metadata);

            } catch (\Exception $e) {
                $this->saveLog("[ImageHelper] {$e->getMessage()}");
                $this->saveLog($e->getTraceAsString());
                unlink($imageFilePath);
                throw $e;
            }
        }

        return $image;
    }

    /**
     * Generate image thumbnail
     *
     * @param \AppBundle\Entity\Image $image
     * @param $metadata
     */
    public function generateThumbnail(\AppBundle\Entity\Image &$image, $metadata)
    {
        if (isset($metadata['SourceFile'])) {
            $filename = $metadata['SourceFile'];
            $thumb = imagecreatetruecolor(100, 75);

            switch ($image->type) {
                case 'JPEG':
                    $source = imagecreatefromjpeg($filename);
                    break;
                case 'JPG':
                case 'PNG':
                    $source = imagecreatefrompng($filename);
                    break;
                case 'SVG':
                    $source = null;
                    break;
                default:
                    $source = null;
            }

            if ($source) {
                imagecopyresized(
                    $thumb, $source,
                    0, 0, 0, 0, 100, 75,
                    $image->width, $image->height
                );

                $thumbnailPath = 'thumb_' . $image->filename;

                imagejpeg($thumb, $this->getParameter('image_thumbnail_directory') . $thumbnailPath, 100);

                $image->thumbnail = $thumbnailPath;
            }
        }
    }

    /**
     * Save image entity
     *
     * @param \DOMElement $element
     * @param $imageFilePath
     * @param $src
     * @param array $metadata
     * @return \AppBundle\Entity\Image|null
     * @throws OptimisticLockException
     */
    protected function saveImage(\DOMElement $element, $imageFilePath, $src, array $metadata = [])
    {
        $image = new \AppBundle\Entity\Image($src);
        $image->alt = $element->getAttribute('alt');
        $image->filename = isset($metadata['System:FileName']) ? $metadata['System:FileName'] : (pathinfo($src,
                PATHINFO_FILENAME) . '.' . pathinfo($src, PATHINFO_EXTENSION));
        $image->path = isset($metadata['System:Directory']) ? substr($metadata['System:Directory'],
            strpos($metadata['System:Directory'], 'images')) : null;
        $image->width = isset($metadata['File:ImageWidth']) ? $metadata['File:ImageWidth'] : 0;
        $image->height = isset($metadata['File:ImageHeight']) ? $metadata['File:ImageHeight'] : 0;
        $image->type = isset($metadata['File:FileType']) ? $metadata['File:FileType'] : pathinfo($src,
            PATHINFO_EXTENSION);
        $image->description = $this->extractImageDescription($element, $image->filename);

        // Extract location coordinates
        $this->extractMetadataCoordinates($image, $metadata);

        // Set metadata
        $image->setMetadata($metadata);

        // Generate thumbnail
        $this->generateThumbnail($image, $metadata);

        // Extract URL domain
        $this->extractDomain($image);

        // Run geocoding
        $this->geocode($image);

        if ($this->isValid($image)) {
            // Save to database
            $this->em->persist($image);
            $this->em->flush($image);
        } else {
            // Remove physical file
            unlink($imageFilePath);
            return null;
        }

        return $image;
    }

    public function extractMetadataCoordinates(\AppBundle\Entity\Image &$image, array $metadata)
    {
        $latitude = isset($metadata['GPS:GPSLatitude']) ? (float)$metadata['GPS:GPSLatitude'] : null;
        $latRef = isset($metadata['GPS:GPSLatitudeRef']) ? strtolower($metadata['GPS:GPSLatitudeRef']) : null;
        $longitude = isset($metadata['GPS:GPSLongitude']) ? (float)$metadata['GPS:GPSLongitude'] : null;
        $lngRef = isset($metadata['GPS:GPSLongitudeRef']) ? strtolower($metadata['GPS:GPSLongitudeRef']) : null;

        if (!is_null($latitude) && !is_null($longitude)) {
            $image->isExifLocation = true;
            $image->isLocationCorrect = true;
        } else {
            $image->isExifLocation = false;
        }

        if ($latitude) {
            if ($latRef == 's' || $latRef == 'south') {
                $image->latitude = 0 - $latitude;
            } else {
                $image->latitude = $latitude;
            }
        }

        if ($longitude) {
            if ($lngRef == 'w' || $lngRef == 'west') {
                $image->longitude = 0 - $longitude;
            } else {
                $image->longitude = $longitude;
            }
        }
    }

    /**
     * @param \AppBundle\Entity\Image $image
     */
    protected function geocode(\AppBundle\Entity\Image &$image)
    {
        if ($image->latitude && $image->longitude) {
            $client = new Client([
                'timeout' => 60,
                'allow_redirects' => false,
                'verify' => $this->getContainer()->getParameter('http_verify_ssl')
            ]);

            $response = $client->get($this->getContainer()->getParameter('google_geocode_url'), [
                'query' => [
                    'latlng' => "{$image->latitude},{$image->longitude}",
                    'key' => $this->getContainer()->getParameter('google_map_api_key'),
                    'result_type' => "street_address|postal_code|country"
                ]
            ]);

            $results = $response->getBody()->getContents();
            $resultObj = @json_decode($results);

            if ($resultObj->status === "OK" && is_array($resultObj->results) && !empty($resultObj->results)) {
                $image->address = $resultObj->results[0]->formatted_address;
                $image->geoparsed = true;
            } elseif ($resultObj->status === 'INVALID_REQUEST') {
                if (!$image->geoparserRetries) {
                    $image->geoparserRetries = 0;
                } else {
                    $image->geoparserRetries += 1;
                }
            }
        }
    }

    /**
     * @param \AppBundle\Entity\Image $image
     */
    protected function extractDomain(\AppBundle\Entity\Image &$image)
    {
        $src = $image->src;
        $host = parse_url($src, PHP_URL_HOST);

        if ($host && !empty($host)) {
            $domain = substr($host, strrpos($host, '.') + 1);
            $image->domain = $domain;
        }
    }

    /**
     * Extract image description
     *
     * @param \DOMElement $element
     * @param $alt
     * @param $filename
     * @return string
     */
    protected function extractImageDescription(\DOMElement $element, $filename)
    {
        $alt = trim($element->getAttribute('alt'));
        $title = trim($element->getAttribute('title'));
        $description = "{$alt} {$title} {$this->sanitize($filename)}";
        $prev = $element->previousSibling;
        $next = $element->nextSibling;

        if (!is_null($prev) && get_class($prev) === \DOMElement::class && $prev->tagName !== 'script') {
            $textContent = $prev->textContent;
            $description .= " " . trim(strip_tags($textContent));
        }

        if (!is_null($next) && get_class($next) === \DOMElement::class && $next->tagName !== 'script') {
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

        $metadata = array_merge($exif->getRawData(), $exif->getData());
        list($width, $height) = @getimagesize($filename);

        if (!isset($metadata['File:ImageWidth']) || empty($metadata['File:ImageWidth'])) {
            $metadata['File:ImageWidth'] = $width;
        }

        if (!isset($metadata['File:ImageHeight']) || empty($metadata['File:ImageHeight'])) {
            $metadata['File:ImageHeight'] = $height;
        }

        return $metadata;
    }

    /**
     * Check whether the image is valid
     *
     * @param \AppBundle\Entity\Image $image
     * @return bool
     */
    protected function isValid(\AppBundle\Entity\Image $image)
    {
        // Check image ratio against standard aspect ratios
        $width = $image->width;
        $height = $image->height;

        if (!$width || !$height) {
            $this->saveLog("[ImageHelper] Image resolutions must not be empty!");
            return false;
        }

        $allowedRatios = $this->getContainer()->getParameter('allowed_aspect_ratios');
        $ratio = $this->getRatio($width, $height);

        // Only proceed further if image size is larger than 400px in each dimension and aspect ratio is valid
        if ($width < $this->getParameter('image_min_width')
            && $height < $this->getParameter('image_min_height')
            && in_array($ratio, $allowedRatios)
        ) {
            $this->saveLog("[ImageHelper] Invalid aspect ratio. The image resolution is {$width} x {$height}.");
            return false;
        }

        // Check if image description contains invalid keywords
        $nonRepImageKeywords = $this->getParameter('non_rep_image_keywords');

        foreach($nonRepImageKeywords as $keyword) {
            if (preg_match('/('. $keyword . ')/i', $image->description)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get image ratio from width and height
     *
     * @param $width
     * @param $height
     * @return float
     */
    protected function getRatio($width, $height)
    {
        $ratio = $width / $height;

        return (float)number_format($ratio, 2, '.', '');
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
