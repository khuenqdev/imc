<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 10/26/2017
 * Time: 3:19 PM
 */

namespace AppBundle\Services\Helpers;


class Url
{
    public function parse($href, $baseUrl)
    {
        $baseUrl = $this->getBaseUrl($baseUrl);

        if (preg_match('^[#(javascript:)(mailto:)(data)]', $href)) {
            return false;
        }

        /*if (strpos($href, '#') !== FALSE) { // Handle anchor link
            return ''; // No crawling at all!
        } elseif (substr($href, 0, 11) == 'javascript:') { // Handle link contains javascript code
            return ''; // No crawling at all!
        } elseif (substr($href, 0, 7) == 'mailto:') { // Handle link contains javascript code
            return ''; // No crawling at all!
        } elseif (substr($href, 0, 1) == '/' && strlen($href) == 1) {
            return ''; // No crawling at all!
        } else if (substr($href, 0, 3) == 'data') {
            return ''; // No crawling at all!
        } else if ($href === 'http://' || $href === 'https://') {
            return ''; // No crawling at all!
        } elseif (substr($href, 0, 2) == '//') { // Handle link with double slashes, e.g. //test.html
            return $urlComponents['scheme'] . ':' . $href;
        } elseif (substr($href, 0, 1) == '/' && substr($href, 0, 2) != '/') {
            return $baseUrl . $href;
        } elseif (substr($href, 0, 1) == './') { // Handle link with a point and a slash, e.g. ./test.html
            return $baseUrl . dirname($urlComponents['path']) . substr($href, 1);
        } elseif (substr($href, 0, 3) == '../') { // Handle link with double dots and a slash, e.g. ../test.html
            return $baseUrl . '/' . $href;
        } elseif (substr($href, 0, 5) != 'https' && substr($href, 0, 4) != 'http') { // Handle link without URL scheme
            return $baseUrl . '/' . $href;
        }*/

        // Return original link if it's a standard url
        return $href;
    }

    /**
     * Get a base URL from an input URL
     *
     * Example:
     *  Input: http://www.example.com/some/thing/here?query=123
     *  Output: http://www.example.com
     *
     * @param $url
     * @return string
     */
    public function getBaseUrl($url)
    {
        // Get URL's components from the base url
        $urlComponents = parse_url($url);
        return $urlComponents['scheme'] . '://' . $urlComponents['host'];
    }
}