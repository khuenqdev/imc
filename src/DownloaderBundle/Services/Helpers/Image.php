<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/21/2017
 * Time: 8:19 PM
 */

namespace DownloaderBundle\Services\Helpers;

use AppBundle\Entity\Link;
use GuzzleHttp\Client;
use PHPExif\Adapter\Exiftool;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpKernel\KernelInterface as Kernel;

class Image
{
    const IMG_DIR = 'images';
    const MIN_WIDTH = 400;
    const MIN_HEIGHT = 400;

    /**
     * Allowed image aspect ratio
     *
     * @var array
     */
    protected $allowedAspectRatio;

    /**
     * Allowed image extensions
     *
     * @var array
     */
    protected $allowedImageExtensions = ['jpg', 'jpeg', 'png'];

    /**
     * @var Kernel
     */
    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->allowedAspectRatio = $this->initializeAspectRatios();
    }

    /**
     * Download an image
     *
     * @param Link $source
     * @param $src
     * @return bool
     */
    public function download(Link $source, $src)
    {
        $imagePath = pathinfo($src);

        if (!isset($imagePath['filename'])
            || !isset($imagePath['extension'])
            || !in_array($imagePath['extension'], $this->allowedImageExtensions)) {
            return false;
        }

        // Download the file using guzzle client
        $client = new Client([
            'timeout' => 0,
            'allow_redirects' => false
        ]);

        if ($saveDir = $this->getDirectory($source->url)) {
            $filename = $saveDir . $imagePath['filename'] . "." . $imagePath['extension'];

            try {
                $client->get($src, ['sink' => $filename]);

                if ($this->validate($filename)) {
                    list($latitude, $longitude) = $this->getLocationCoordinates($filename);

                    $image = new \AppBundle\Entity\Image($source, $src);
                    $image->latitude = $latitude;
                    $image->longitude = $longitude;

                    dump($latitude);
                    dump($longitude);

                    return true;
                }

            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Extract image location data
     *
     * @param $filename
     * @return array
     */
    protected function getLocationCoordinates($filename)
    {
        $toolPath = $this->kernel->getRootDir() . "/../exiftool/";

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

        $rawData = $exif->getRawData();
        $latitude = isset($rawData['GPS:GPSLatitude']) ? $rawData['GPS:GPSLatitude'] : null;
        $longitude = isset($rawData['GPS:GPSLongitude']) ? $rawData['GPS:GPSLongitude'] : null;

        return [$latitude, $longitude];
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
        if (($width < self::MIN_WIDTH && $height < self::MIN_HEIGHT)
            || !in_array($ratio, $this->allowedAspectRatio)
        ) {
            unlink($filename);
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
        $savePath = $this->kernel->getRootDir() . "/../" . self::IMG_DIR . "/" . $source . "/";

        if (!file_exists($savePath)) {
            $canMkdir = @mkdir($savePath);
            if (!$canMkdir) {
                return "";
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
}