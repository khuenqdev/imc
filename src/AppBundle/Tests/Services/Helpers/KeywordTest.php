<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/12/2017
 * Time: 3:44 PM
 */

namespace AppBundle\Tests\Services\Helpers;


use AppBundle\Services\Helpers\Keyword;

class KeywordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Keyword
     */
    private $service;

    public function setUp()
    {
        $this->service = new Keyword();
    }

    /**
     * Test text purification
     *
     * @dataProvider purifyProvider
     * @param $inputText
     * @param $expected
     */
    public function testPurify($inputText, $expected)
    {
        $this->assertEquals($expected, $this->service->purify($inputText));
    }

    /**
     * Tokenize a string
     *
     * @return array
     */
    public function testTokenize()
    {
        $string = "This is a test string to be tokenized. How does it look? AAAA";
        $tokens = $this->service->tokenize($string);
        $this->assertSame([
            'This', 'is', 'a', 'test', 'string',
            'to', 'be', 'tokenized', 'How', 'does',
            'it', 'look', 'AAAA'
        ], $tokens);

        return $tokens;
    }

    /**
     * Test normalize a set of tokens
     *
     * @depends testTokenize
     * @param $tokens
     */
    public function testNormalize($tokens)
    {
        $normalizedTokens = $this->service->normalize($tokens);

        $this->assertSame([
            'this', 'is', 'a', 'test', 'string',
            'to', 'be', 'tokenized', 'how', 'does',
            'it', 'look', 'aaaa'
        ], $normalizedTokens);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function purifyProvider()
    {
        return [
            ["Less than < and greater than >", "Less than and greater than "],
            ["Less than &lt; and greater than &gt;", "Less than and greater than "],
            ["&nbsp; as space", "  space"],
            ["\"Some quoted string\"", "Some quoted string"],
            ["Special characters @\$\£", "Special characters  "],
        ];
    }
}