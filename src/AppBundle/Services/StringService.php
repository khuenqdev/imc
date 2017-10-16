<?php

namespace AppBundle\Services;

/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:46
 */
class StringService
{
    /**
     * Trim Slashes
     *
     * Removes any leading/trailing slashes from a string
     *
     * @param $str
     * @return string
     */
    public function trimSlashes($str)
    {
        return trim($str, '/');
    }

    /**
     * Strip Slashes
     *
     * Removes slashes contained in a string or in an array
     *
     * @param $str
     * @return array|string
     */
    public function stripSlashes($str)
    {
        if (!is_array($str)) {
            return stripslashes($str);
        }

        foreach ($str as $key => $val) {
            $str[$key] = stripSlashes($val);
        }

        return $str;
    }

    /**
     * Strip Quotes
     *
     * Removes single and double quotes from a string
     *
     * @param string
     * @return string
     */
    public function stripQuotes($str)
    {
        return str_replace(array('"', "'"), '', $str);
    }

}