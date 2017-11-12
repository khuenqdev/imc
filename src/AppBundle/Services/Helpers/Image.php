<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/30/2017
 * Time: 3:29 PM
 */

namespace AppBundle\Services\Helpers;


use Symfony\Component\HttpKernel\Kernel;

class Image
{
    const METADATA_EXIF = 'EXIF';
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

    public function extractMetadata($type = self::METADATA_EXIF)
    {

    }

    public function download($host, $src)
    {
        // Split the image src to get its path information
        $imagePath = pathinfo($src);

        if (!isset($imagePath['filename'])
            || !isset($imagePath['extension'])
            || !in_array($imagePath['extension'], $this->allowedImageExtensions)) {
            return false;
        }

        // Initialize cURL object
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $src);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Request image content
        $response = curl_exec($ch);
        curl_close($ch);

        $savePath = $this->prepareDirectory($host);

        if (empty($savePath)) {
            return false;
        }

        // Write image content to server storage
        $filename = $savePath . $imagePath['filename'] . "." . $imagePath['extension'];
        $file = file_put_contents($filename, $response);

        if ($file === false) {
            return false;
        }

        list($width, $height) = @getimagesize($filename);

        // Only proceed further if image size is larger than 400px in each dimension and
        // follow standard photography aspect ratios
        $ratio = floatval($width / $height);
        if (($width < self::MIN_WIDTH && $height < self::MIN_HEIGHT)
            || !in_array($ratio, $this->allowedAspectRatio)
        ) {
            unlink($filename);
            return "";
        }

        return true;
    }

    /**
     * Prepare directory for saving
     *
     * @param $host
     * @return string
     */
    protected function prepareDirectory($host)
    {
        $save_path = $this->kernel->getRootDir() . "/" . self::IMG_DIR . "/" . $host . "/";

        if (!file_exists($save_path)) {
            $canMkdir = mkdir($save_path);
            if (!$canMkdir) {
                return "";
            }
        }

        return $save_path;
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