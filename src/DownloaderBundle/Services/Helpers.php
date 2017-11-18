<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/18/2017
 * Time: 11:23 PM
 */

namespace DownloaderBundle\Services;

use DownloaderBundle\Services\Helpers\Keyword;

class Helpers
{
    public $keyword;

    public function __construct(Keyword $keyword)
    {
        $this->keyword = $keyword;
    }
}