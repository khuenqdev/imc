<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 11:23 PM
 */

namespace DownloaderBundle\Services;

use DownloaderBundle\Services\Helpers\Keyword;
use DownloaderBundle\Services\Helpers\Url;

class Helpers
{
    public $keyword;
    public $url;

    public function __construct(Keyword $keyword, Url $url)
    {
        $this->keyword = $keyword;
        $this->url = $url;
    }
}