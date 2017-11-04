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
    /**
     * Parse a href to receive a valid URL
     *
     * If base url
     *
     * @param $href
     * @param $baseUrl
     * @return bool|string
     */
    public function parse($href, $baseUrl)
    {
        if (preg_match('/(#|\bjavascript:\b|\bmailto:\b|\bdata:\b)/', $href)) {
            return false;
        }

        $hrefComponents = parse_url($href);

        if ($hrefComponents !== FALSE) {
            // Get URL's components from the base url
            $urlComponents = parse_url($baseUrl);
            $baseUrl = $urlComponents['scheme'] . '://' . $urlComponents['host'];

            if (substr($href, 0, 1) == '/' && strlen($href) == 1) { // When the href has only one slash
                return ''; // No crawling at all!
            } else if ($href === 'http://' || $href === 'https://') { // When the href has only http scheme
                return ''; // No crawling at all!
            } elseif (substr($href, 0, 2) == '//') { // Handle href with double slashes, e.g. //test.html
                return $urlComponents['scheme'] . ':' . $href;
            } elseif (substr($href, 0, 1) == '/' && substr($href, 0, 2) != '/') {
                return $baseUrl . $href; // When the href has only two slashes
            } elseif (substr($href, 0, 1) == './') { // Handle href with a point and a slash, e.g. ./test.html
                return $baseUrl . dirname($urlComponents['path']) . substr($href, 1);
            } elseif (substr($href, 0, 3) == '../') { // Handle href with double dots and a slash, e.g. ../test.html
                return $baseUrl . '/' . $href;
            } elseif (substr($href, 0, 5) != 'https' && substr($href, 0, 4) != 'http' && isset($hrefComponents['path'])) {
                return $baseUrl . '/' . $href; // Handle href without URL scheme but still have valid path
            } elseif (substr($href, 0, 5) != 'https' && substr($href, 0, 4) != 'http') { // Handle href without URL scheme
                return $urlComponents['scheme'] . '://' . $href;
            }

            if ((isset($hrefComponents['query']) && !empty($hrefComponents['query']))
                || (isset($hrefComponents['fragment']) && !empty($hrefComponents['fragment']))
            ) {
                // Strip off query string
                $path = isset($hrefComponents['path']) ? ('/' . $hrefComponents['path']) : '';
                return $hrefComponents['scheme'] . '://' . $hrefComponents['host'] . $path;
            }

            // Return original href if it's a standard url
            return $href;
        }

        return false;
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