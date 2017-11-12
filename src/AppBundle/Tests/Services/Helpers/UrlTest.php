<?php

/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/26/2017
 * Time: 4:05 PM
 */

namespace AppBundle\Tests\Helpers;

use AppBundle\Services\Helpers\Url;
use PHPUnit_Framework_TestCase;

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $service;

    public function setUp()
    {
        $this->service = new Url();
    }

    /**
     * Test parsing href to url
     *
     * @dataProvider parseProvider
     * @param $baseUrl
     * @param $href
     * @param $expected
     */
    public function testParse($baseUrl, $href, $expected)
    {
        $this->assertEquals($expected, $this->service->parse($href, $baseUrl));
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public function parseProvider()
    {
        $baseUrl = 'https://techviral.net/';

        return [
            [$baseUrl, 'https://techviral.net/amazing-things-you-can-do-after-rooting-android/', 'https://techviral.net/amazing-things-you-can-do-after-rooting-android/'],
            [$baseUrl, 'mailto:admin@techviral.net', false],
            [$baseUrl, 'javascript:void(0);', false],
            [$baseUrl, 'data:test_data', false],
            [$baseUrl, '#something', false],
            [$baseUrl, 'example.com', 'https://example.com'],
        ];
    }
}