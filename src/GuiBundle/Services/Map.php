<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 12-Dec-17
 * Time: 21:13
 */

namespace GuiBundle\Services;

use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Map as GoogleMap;

class Map
{
    private $mapHelper;
    private $apiHelper;

    public function __construct($apiKey)
    {
        $this->mapHelper = MapHelperBuilder::create()->build();
        $this->apiHelper = ApiHelperBuilder::create()
            ->setKey($apiKey)
            ->build();
    }

    public function render()
    {
        $map = new GoogleMap();

    }
}