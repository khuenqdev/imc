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

    public function __construct(Kernel $kernel, EntityManager $em)
    {
        $this->kernel = $kernel;
        $this->em = $em;
        $this->allowedAspectRatio = $this->initializeAspectRatios();
    }

    /**
     * Download an image
     *
     * @param Link $source
     * @param $src
     * @param $alt
     * @return bool
     */
    public function download(Link $source, $src, $alt = "")
    {
        $imagePath = pathinfo($src);

        if (!isset($imagePath['filename'])
            || !isset($imagePath['extension'])
            || !in_array($imagePath['extension'], $this->getParameter('allowed_image_extensions'))) {
            return false;
        }

        // Download the file using guzzle client
        $client = new Client([
            'timeout' => 0,
            'allow_redirects' => false,
            'verify' => $this->getParameter('http_verify_ssl')
        ]);

        if ($saveDir = $this->getDirectory($source->url)) {
            $filename = $saveDir . $imagePath['filename'] . "." . $imagePath['extension'];

            try {
                $client->get($src, ['sink' => $filename]);

                if ($this->validate($filename)) {
                    $this->saveImage($source, $src, $alt, $this->getMetadata($filename));
                    return true;
                }
            } catch (\Exception $e) {
                $this->errorMessage .= $e->getMessage() . "\n";
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
     * @param $source
     * @param $src
     * @param string $alt
     * @param array $metadata
     * @return $this
     */
    protected function saveImage($source, $src, $alt = '', array $metadata)
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
        $image->setMetadata($metadata);

        if ($image->latitude && $image->longitude) {
            $this->determineImageLocation($image, $image->latitude, $image->longitude);
        }

        // Save to database
        $this->em->persist($source);
        $this->em->flush($source);
        $this->em->persist($image);
        $this->em->flush($image);

        return $this;
    }

    /**
     * Determine image's location
     *
     * @param \AppBundle\Entity\Image $image
     * @param $latitude
     * @param $longitude
     */
    protected function determineImageLocation(\AppBundle\Entity\Image &$image, $latitude, $longitude)
    {
        $client = new Client([
            'base_uri' => $this->getParameter('google_geocode_url'),
            'verify' => $this->getParameter('http_verify_ssl')
        ]);

        try {
            $response = $client->get('', [
                'query' => [
                    'latlng' => "{$latitude},{$longitude}",
                    'key' => $this->getParameter('google_geocode_key'),
                    'result_type' => "street_address|postal_code|country"
                ]
            ]);

            $results = $response->getBody()->getContents();
            $resultObj = \GuzzleHttp\json_decode($results);

            if ($resultObj->status === "OK" && is_array($resultObj->results) && !empty($resultObj->results)) {
                $image->address = $resultObj->results[0]->formatted_address;
                foreach ($resultObj->results[0]->address_components as $component) {
                    if (in_array('postal_code', $component->types)) {
                        $image->zipcode = $component->long_name;
                    }

                    if (in_array('country', $component->types)) {
                        $image->country = $component->long_name;
                    }
                }
            }
        } catch (\Exception $e) {

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
            $this->errorMessage .= "Invalid aspect ratio for image {$filename}. The image dimension is {$width} x {$height}.\n";
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
        $imagePath = $this->kernel->getRootDir() . "/../" . $this->getParameter('image_directory') . "/";

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
}