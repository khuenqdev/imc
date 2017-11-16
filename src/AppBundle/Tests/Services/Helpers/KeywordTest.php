<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 11/12/2017
 * Time: 3:44 PM
 */

namespace AppBundle\Tests\Services\Helpers;

use AppBundle\Services\Helpers\Keyword;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KeywordTest extends KernelTestCase
{
    /**
     * @var Keyword
     */
    private $service;

    public function setUp()
    {
        self::bootKernel();
        $this->service = new Keyword(self::$kernel->getContainer()->get('doctrine.orm.entity_manager'));
    }

    /**
     * Extract keywords from a text
     */
    public function testExtractKeywordsFromText()
    {
        $text = "Pop outfits Little Glee Monster, Hey! Say! Jump and Twice will grace the stage for the first time at this year’s “Kohaku Uta Gassen,” with NHK appearing to court new viewers for its annual end-of-year musical extravaganza.

Other artists making “Kohaku” debuts include singers Midori Oka, Daichi Miura and Tortoise Matsumoto, and rock acts Shishamo, Elephant Kashimashi, Wanima and Takehara Pistol.

Enka singer Hiroshi Itsuki will be the night’s veteran, with 46 appearances already under his belt, while Sayuri Ishikawa is set to mark her 40th appearance.

“Kohaku Uta Gassen,” which translates loosely as the “Red and White Song Battle,” began as a radio program in 1951. Male and female acts are split into two teams — White and Red, respectively — to battle it out for votes from a panel of judges and viewers. NHK has also announced it will continue to use last year’s theme, “Yume o Utao” (“Let’s Sing a Dream”), through 2019 in the lead up to the 2020 Tokyo Olympics.

Ratings have dropped since “Kohaku” hit its prime in 1963, when the show earned a staggering 81.4 percent of viewership. Ratings based on the show’s second half in the Kanto area show viewership at 40. 2 percent last year and 39.2 percent the year before.

The weekly Shukan Bunshun released a survey this month that said people were sick of boy bands dominating the show — but their complaints weren’t reflected in this year’s lineup. That survey also named singer Namie Amuro as the top choice among performers that viewers hoped they’d see, as she is set to retire from showbiz next year. However, the 40-year-old pop star’s name was not on the roster, a fact that led her fans to voice their displeasure on social media.

This year’s team leaders, or hosts, are “Sekigahara” actress Kasumi Arimura for the Red team and Arashi member Kazunari Ninomiya for the White team.";

        $keywords = $this->service->extractKeywordsFromText($text);

        $this->assertNotEmpty($keywords);
        var_dump($this->service->countWordOccurrence('Kohaku', $text));
    }

    /**
     * Extract keywords from a string
     */
    public function testExtractKeywordsFromString()
    {
        $string1 = "Shibuya expo showcases innovations toward creating an inclusive society";
        $string2 = "Peace speech in Geneva by Japanese student was canceled due to China pressure: government sources";
        $string3 = "The colorful buildings at the coast of the \"Cinque Terre\"";

        $keywords1 = $this->service->extractKeywordsFromString($string1);
        $this->assertNotEmpty($keywords1);
        var_dump($keywords1);

        $keywords2 = $this->service->extractKeywordsFromString($string2);
        $this->assertNotEmpty($keywords2);
        var_dump($keywords2);

        $keywords3 = $this->service->extractKeywordsFromString($string3);
        $this->assertNotEmpty($keywords3);
        var_dump($keywords3);
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
    public function testNormalizeTokens($tokens)
    {
        $normalizedTokens = $this->service->normalizeTokens($tokens);

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
            ["Less than < and greater than >", "Less than   and greater than  "],
            ["Less than &lt; and greater than &gt;", "Less than  and greater than "],
            ["&nbsp; as space", "  as space"],
            ["\"Some quoted string\"", "Some quoted string"],
            ["Special characters @\$\£", "Special characters    "],
        ];
    }
}