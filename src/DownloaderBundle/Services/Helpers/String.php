<?php

namespace DownloaderBundle\Services\Helpers;

/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 19-Oct-17
 * Time: 19:46
 */
class String
{
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

    /**
     * Quotes to Entities
     *
     * Converts single and double quotes to entities
     *
     * @param    string
     * @return    string
     */
    public function quotesToEntities($str)
    {
        return str_replace(array("\'", "\"", "'", '"'), array("&#39;", "&quot;", "&#39;", "&quot;"), $str);
    }

    /**
     * Reduce Double Slashes
     *
     * Converts double slashes in a string to a single slash,
     * except those found in http://
     *
     * http://www.some-site.com//index.php
     *
     * becomes:
     *
     * http://www.some-site.com/index.php
     *
     * @param    string
     * @return    string
     */
    public function reduceDoubleSlashes($str)
    {
        return preg_replace('#(^|[^:])//+#', '\\1/', $str);
    }

    /**
     * Reduce Multiples
     *
     * Reduces multiple instances of a particular character.  Example:
     *
     * Fred, Bill,, Joe, Jimmy
     *
     * becomes:
     *
     * Fred, Bill, Joe, Jimmy
     *
     * @param string $str
     * @param string $character the character you wish to reduce
     * @param bool $trim TRUE/FALSE - whether to trim the character from the beginning/end
     * @return string
     */
    function reduceMultiples($str, $character = ',', $trim = FALSE)
    {
        $str = preg_replace('#' . preg_quote($character, '#') . '{2,}#', $character, $str);
        return ($trim === TRUE) ? trim($str, $character) : $str;
    }

    /**
     * Create a "Random" String
     *
     * @param string $type type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
     * @param int $len number of characters
     * @return string
     */
    public function randomString($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }

    /**
     * Add's _1 to a string or increment the ending number to allow _2, _3, etc
     *
     * @param string $str required
     * @param string $separator What should the duplicate number be appended with
     * @param string $first Which number should be used for the first dupe increment
     * @return string
     */
    public function incrementString($str, $separator = '_', $first = 1)
    {
        preg_match('/(.+)' . preg_quote($separator, '/') . '([0-9]+)$/', $str, $match);
        return isset($match[2]) ? $match[1] . $separator . ($match[2] + 1) : $str . $separator . $first;
    }

    /**
     * Alternator
     *
     * Allows strings to be alternated.
     *
     * @param	string (as many parameters as needed)
     * @return	string
     */
    public function alternator()
    {
        static $i;

        if (func_num_args() === 0)
        {
            $i = 0;
            return '';
        }

        $args = func_get_args();
        return $args[($i++ % count($args))];
    }
}