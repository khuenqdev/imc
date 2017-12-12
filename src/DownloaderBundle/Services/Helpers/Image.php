<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:19 PM
 */

namespace DownloaderBundle\Services\Helpers;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Monolog\Logger;
use PHPExif\Adapter\Exiftool;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpKernel\KernelInterface as Kernel;

class Image
{
    /**
     * Allowed image aspect ratio
     *
     * @var array
     */
    protected $allowedAspectRatio;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $errorMessage = "";

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Kernel $kernel, EntityManager $em, Logger $logger)
    {
        $this->kernel = $kernel;
        $this->em = $em;
        $this->allowedAspectRatio = $this->initializeAspectRatios();
        $this->logger = $logger;
        $this->client = new Client([
            'timeout' => 3,
            'allow_redirects' => false,
            'verify' => $this->getParameter('http_verify_ssl')
        ]);
    }

    /**
     * Download an image
     *
     * @param \DOMElement $element
     * @param Link $source
     * @param $src
     * @param string $alt
     * @return bool
     */
    public function download(\DOMElement $element, Link $source, $src, $alt = "")
    {
        $imagePath = pathinfo($src);

        if (!isset($imagePath['filename'])
            || !isset($imagePath['extension'])
            || !in_array($imagePath['extension'], $this->getParameter('allowed_image_extensions'))
        ) {
            return false;
        }

        // Download the image file
        if ($saveDir = $this->getDirectory($source->url)) {
            $filename = $saveDir . $imagePath['filename'] . "." . $imagePath['extension'];

            try {
                $this->client->get($src, ['sink' => $filename]);

                if ($this->validate($filename)) {
                    $this->saveImage($element, $source, $src, $alt, $this->getMetadata($filename));
                    return true;
                }
            } catch (\Exception $e) {
                $this->saveLog("[Image Error] " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Get error message (if any)
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Save image entity
     *
     * @param \DOMElement $element
     * @param $source
     * @param $src
     * @param string $alt
     * @param array $metadata
     * @return $this
     */
    protected function saveImage(\DOMElement $element, $source, $src, $alt = '', array $metadata)
    {
        // Avoid duplication of image
        if ($this->em->getRepository(\AppBundle\Entity\Image::class)
            ->findOneBy(['src' => $src])
        ) {
            return $this;
        }

        $image = new \AppBundle\Entity\Image($source, $src, $alt);
        $image->filename = isset($metadata['System:FileName']) ? $metadata['System:FileName'] : null;
        $image->path = isset($metadata['System:Directory']) ? substr($metadata['System:Directory'], strpos($metadata['System:Directory'], 'images')) : null;
        $image->width = isset($metadata['File:ImageWidth']) ? $metadata['File:ImageWidth'] : null;
        $image->height = isset($metadata['File:ImageHeight']) ? $metadata['File:ImageHeight'] : null;
        $image->latitude = isset($metadata['GPS:GPSLatitude']) ? $metadata['GPS:GPSLatitude'] : null;
        $image->longitude = isset($metadata['GPS:GPSLongitude']) ? $metadata['GPS:GPSLongitude'] : null;
        $image->altitude = isset($metadata['GPS:GPSAltitude']) ? $metadata['GPS:GPSAltitude'] : null;
        $image->type = isset($metadata['File:FileType']) ? $metadata['File:FileType'] : null;
        $image->copyright = isset($metadata['copyright']) ? $metadata['copyright'] : null;
        $image->dateTaken = isset($metadata['creationdate']) ? $metadata['creationdate'] : null;
        $image->dateAcquired = new \DateTime(date('Y-m-d H:i:s'));
        $image->author = isset($metadata['author']) ? $metadata['author'] : null;
        $image->isExifLocation = !empty($image->latitude) && !empty($image->longitude);
        $image->description = $this->extractImageDescription($element, $alt, $image->filename);
        $image->setMetadata($metadata);

        if ($image->latitude && $image->longitude) {
            $this->determineImageAddress($image, $image->latitude, $image->longitude);
        } else {
            $this->determineImageLocation($image);
        }

        // Save to database
        try {
            $this->em->persist($image);
            $this->em->flush($image);
        } catch (\Exception $e) {
            $this->saveLog("[Image Error] " . $e->getMessage());
        }

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

        return $description;
    }

    /**
     * Determine image's GPS location and address based on its description
     *  - alt attribute
     *  - text from surrounded elements
     *
     * @param \AppBundle\Entity\Image $image
     */
    protected function determineImageLocation(\AppBundle\Entity\Image &$image)
    {
        try {
            $response = $this->client->get($this->getParameter('geoparser_url'), [
                'query' => [
                    'scantext' => $image->description,
                    'json' => 1
                ]
            ]);

            $results = $response->getBody()->getContents();
            $resultObj = @json_decode($results);

            if ($resultObj->matches !== null) {
                if (is_array($resultObj->match)) {
                    $match = $resultObj->match[0];
                } else {
                    $match = $resultObj->match;
                }

                $image->address = $match->location;
                $image->latitude = $resultObj->latt;
                $image->longitude = $resultObj->longt;
                $image->isExifLocation = false;
            }

        } catch (\Exception $e) {
            $this->saveLog("[Image Error] " . $e->getMessage());
        }
    }

    /**
     * Determine image's address
     *
     * @param \AppBundle\Entity\Image $image
     * @param $latitude
     * @param $longitude
     */
    protected function determineImageAddress(\AppBundle\Entity\Image &$image, $latitude, $longitude)
    {
        try {
            $response = $this->client->get($this->getParameter('google_geocode_url'), [
                'query' => [
                    'latlng' => "{$latitude},{$longitude}",
                    'key' => $this->getParameter('google_map_api_key'),
                    'result_type' => "street_address|postal_code|country"
                ]
            ]);

            $results = $response->getBody()->getContents();
            $resultObj = @json_decode($results);

            if ($resultObj->status === "OK" && is_array($resultObj->results) && !empty($resultObj->results)) {
                $image->address = $resultObj->results[0]->formatted_address;
            }
        } catch (\Exception $e) {
            $this->saveLog("[Image Error] " . $e->getMessage());
        }
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
     * @param $filename
     * @return bool
     */
    protected function validate($filename)
    {
        list($width, $height) = @getimagesize($filename);

        // Only proceed further if image size is larger than 400px in each dimension and
        // follow standard photography aspect ratios
        $ratio = floatval($width / $height);
        if (($width < $this->getParameter('image_min_width')
                && $height < $this->getParameter('image_min_height'))
            || !in_array($ratio, $this->allowedAspectRatio)
        ) {
            unlink($filename);
            $this->errorMessage = "[Image Error] Invalid aspect ratio for image {$filename}. The image dimension is {$width} x {$height}.\n";
            return false;
        }

        return true;
    }

    /**
     * Get directory for saving
     *
     * @param $source
     * @return string
     */
    protected function getDirectory($source)
    {
        $imagePath = $this->kernel->getRootDir() . "/../web/downloaded/" . $this->getParameter('image_directory') . "/";

        $source = parse_url($source, PHP_URL_HOST);

        if (!$source) {
            return $imagePath;
        }

        $savePath = $imagePath . $source . "/";

        if (!file_exists($savePath)) {
            $canMkdir = @mkdir($savePath);
            if (!$canMkdir) {
                return $imagePath;
            }
        }

        return $savePath;
    }

    /**
     * Initialize standard aspect ratios list
     */
    protected function initializeAspectRatios()
    {
        return [
            floatval(1 / 1),
            floatval(5 / 4),
            floatval(4 / 5),
            floatval(4 / 3),
            floatval(3 / 4),
            floatval(3 / 2),
            floatval(2 / 3),
            floatval(5 / 3),
            floatval(3 / 5),
            floatval(16 / 9),
            floatval(9 / 16),
            floatval(3 / 1),
            floatval(1 / 3),
        ];
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
        return preg_replace('/\W|(\bjpeg\b)|(\bpng\b)|(\bjpg\b)|\_/i', ' ', $filename);
    }

    /**
     * Log error messages
     *
     * @param $message
     */
    protected function saveLog($message)
    {
        $this->errorMessage = $message . "\n";
        $this->logger->debug($message);
    }
}