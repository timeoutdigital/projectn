<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/** 
 * Test XMLDataFixture
 *
 *
 * @package test
 * @subpackage unit.lib
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class xmlDataFixerTest extends PHPUnit_Framework_TestCase
{

    /**
     * Singapore broken-singapore.xml contains a char that is outside simplexml Chars boundary,
     * UTF8_Encode should encode these chars to simplexml compliance
     */
    public function testGetSimpleXMLencodeUTF8()
    {
        $fileData       = file_get_contents( TO_TEST_DATA_PATH . '/xmlDataFixer/broken-singapore.xml' );

        $xmlDataFixer   = new xmlDataFixer( $fileData );

        //$xmlDataFixer->removeHtmlEntiryEncoding();
        $xmlDataFixer->encodeUTF8();

        $xml            = $xmlDataFixer->getSimpleXML();

        $this->assertNotNull( $xml );
        $this->assertType( "SimpleXMLElement", $xml, 'getXml should return SimpleXMLElement' );

    }

    public function testGetSimpleXML()
    {
        $fileData       = file_get_contents( TO_TEST_DATA_PATH . '/xmlDataFixer/perfectxml.xml' );

        $xmlDataFixer   = new xmlDataFixer( $fileData );

        $xml            = $xmlDataFixer->getSimpleXML();

        $this->assertNotNull( $xml );
        $this->assertType( "SimpleXMLElement", $xml, 'getXml should return SimpleXMLElement' );
        
    }

    public function testAddRootElement()
    {
        $fileData       = '<venue><name>Test venue</name></venue><venue><name>Test venue 2</name></venue>';//file_get_contents( TO_TEST_DATA_PATH . '/xmlDataFixer/perfectxml.xml' );

        $xmlDataFixer   = new xmlDataFixer( $fileData );

        $xmlDataFixer->addRootElement( 'venues' );
        
        $xml            = $xmlDataFixer->getSimpleXML();
        
        $this->assertNotNull( $xml );

        $this->assertType( "SimpleXMLElement", $xml, 'getXml should return SimpleXMLElement' );
    }

    public function testAddRootElementWithHeader()
    {
        $fileData       = '<?xml version="1.0" encoding="UTF-8"?> <venue><name>Test venue</name></venue><venue><name>Test venue 2</name></venue>';

        $xmlDataFixer   = new xmlDataFixer( $fileData );

        $xmlDataFixer->addRootElement( 'venues' );

        $xml            = $xmlDataFixer->getSimpleXML();

        $this->assertNotNull( $xml );

        $this->assertType( "SimpleXMLElement", $xml, 'getXml should return SimpleXMLElement' );
    }

    public function testGetSimpleXMLUsingXSLT ()
    {
        $fileData       = '<?xml version="1.0" encoding="UTF-8"?> <root><venue><name>Test venue</name></venue> <event><name>Test Event 1</name></event><event><name>Test Event 2</name></event></root>';

        $xmlDataFixer   = new xmlDataFixer( $fileData );

        $xslt = '<?xml version="1.0" encoding="UTF-8"?>
           <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
            <xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="name" />
            <xsl:template match="/">
              <xsl:element name="events">
                <xsl:for-each select="//root/event">
                                      <xsl:copy-of select="." />
                </xsl:for-each>
              </xsl:element>
            </xsl:template>
          </xsl:stylesheet>';

        $xml            = $xmlDataFixer->getSimpleXMLUsingXSLT( $xslt );

        $this->assertNotNull( $xml );

        $this->assertType( "SimpleXMLElement", $xml, 'getXml should return SimpleXMLElement' );

        $this->assertEquals( 2, count($xml->event) );
        
    }

    public function testRemoveMSWordHtmlTags()
    {
        $xmlString = <<<EOF
   <movie id='14296'>
                <name><![CDATA[Reel life: Time to meet your re-maker]]></name>
                <review><![CDATA[<p>






<!--[if gte mso 9]>

  Normal
  0





  false
  false
  false

  EN-GB
  ZH-TW
  X-NONE














  MicrosoftInternetExplorer4













<![endif]--><!--[if gte mso 9]><![endif]-->
<!--
 /* Font Definitions */
 @font-face
	{
	panose-1:2 2 3 0 0 0 0 0 0 0;
	mso-font-alt:"\!Ps2OcuAe";}
@font-face
	{"Cambria Math";
	panose-1:2 4 5 3 5 4 6 3 2 4;}
@font-face
	{
	panose-1:2 15 5 2 2 2 4 3 2 4;
	mso-font-alt:"Arial Rounded MT Bold";}
@font-face
	{"\@新細明體";
	panose-1:2 2 3 0 0 0 0 0 0 0;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{
	mso-style-parent:"";
	margin:0in;
	margin-bottom:.0001pt;
	font-size:12.0pt;"Times New Roman","serif";}
.MsoChpDefault
	{
	font-size:10.0pt;}
.MsoPapDefault
	{}
 /* Page Definitions */
 @page
	{}
@page Section1
	{size:8.5in 11.0in;
	margin:1.0in 1.25in 1.0in 1.25in;}
div.Section1
	{page:Section1;}
-->
<!--[if gte mso 10]>

 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-style-parent:"";
	font-size:10.0pt;"Calibri","sans-serif";}

<![endif]-->
<p>There are great directors who remade their own films (Hitchcock, Ozu), there are decent films remade into decent films (<i>The Magnificent Seven</i>, <i>The Departed</i>), and there are seemingly pointless remakes that are nonetheless interesting from a critical perspective (Gus Van Sant&rsquo;s <i>Psycho</i> and Michael Haneke&rsquo;s <i>Funny Games</i>). Remakes, then, can be worthwhile &ndash; unless you&rsquo;re talking about Hollywood&rsquo;s cannibalisation of Asian films in recent years.</p>
<p>Thus it was with great pleasure that I discovered that director Benny Chan&rsquo;s new film, <i>Connected</i>, is a remake of a Hollywood film &ndash; we&rsquo;ve finally struck back! But I was baffled all the same, by these people who went through all the trouble to officially remake the less-than-stellar Hollywood film <i>Cellular</i>. I mean, why would anyone throw money away on a script that didn&rsquo;t make much sense the first time around?</p>
<p>Answer: nobody has gone through much trouble, and nobody has thrown away much money, either. &ldquo;It&rsquo;s actually not me, but the investors, Warner China Film, who decided to make this Asian adaptation,&rdquo; explains Chan, one of the luminaries of Hong Kong action cinema. &ldquo;As Warner and New Line Cinema [of the US] are under the same company, it was quite easy to obtain the rights. In fact, the script had already been written up when they approached me. In that script, the story was changed into an investigation of a drug trafficking case, and it was more like a detective story... When I saw that, I decided to make it closer to the original version and adapt for a Hong Kong setting.&rdquo;</p>
<p>I can&rsquo;t help but be a little amused when Chan mentions that he has made effort to eliminate the plot holes in <i>Cellular</i> to make the whole thing more plausible. &ldquo;Of course it takes a little coincidence to make this story work,&rdquo; he adds. &ldquo;Remaking movies is not a bad thing in itself, as you can have a second chance to improve upon the original and put right its flaws.&rdquo;</p>
<p>For me, remaking movies is not a bad thing as long as the original is obscure enough. When asked if he is worried by the audience&rsquo;s familiarity with the original story, Chan says he isn&rsquo;t: &ldquo;as <i>Cellular</i> was not a major blockbuster in Hong Kong, the audience won&rsquo;t be too familiar with the story. In any case, I&rsquo;ve also included some action elements that aren&rsquo;t common in Hollywood films, like the car crashes in the new film.&rdquo; At least Chan has thought about how to make his film better than the original; Hollywood remakes are usually only concerned with making a fistful of dollars.</p>
<p>As the director of Hong Kong&rsquo;s first official remake of a Hollywood film, does Chan see the beginning of a new trend? &ldquo;I think this may only be a special case &ndash; it just so happens that a Hollywood script has attracted the attention of a mainland production company. At the end, it&rsquo;s really down to the investors.&rdquo; </p>
<p><i>Edmund Lee</i></p>
<i>Connected opens on Thu 25.</i>



</p>]]></review>
                <short_description><![CDATA[Hong Kong shows it can strike back at Hollywood.]]></short_description>
                <url><![CDATA[]]></url>
                <timeout_url><![CDATA[http://tohk.testpilotweb.com/film/features/14296/reel-life-time-to-meet-your-re-maker.html]]></timeout_url>
                <rating><![CDATA[]]></rating>

                <tags>
                    <tag><![CDATA[Film]]></tag>
                </tags>


                    <medias>

                    <media>
                        <url><![CDATA[http://www.timeout.com.hk/media/content/normal/2804_reel_life.jpg]]></url>
                    </media>

                    </medias>

        	</movie>
EOF;
        $xmlDataFixer = new xmlDataFixer( $xmlString );
        $xmlDataFixer->removeMSWordHtmlTags();

        // get Clean XML Element
        $xml = $xmlDataFixer->getSimpleXML();

        // Extected review
        $expected = <<<EOF
<p>






<p>There are great directors who remade their own films (Hitchcock, Ozu), there are decent films remade into decent films (<i>The Magnificent Seven</i>, <i>The Departed</i>), and there are seemingly pointless remakes that are nonetheless interesting from a critical perspective (Gus Van Sant&rsquo;s <i>Psycho</i> and Michael Haneke&rsquo;s <i>Funny Games</i>). Remakes, then, can be worthwhile &ndash; unless you&rsquo;re talking about Hollywood&rsquo;s cannibalisation of Asian films in recent years.</p>
<p>Thus it was with great pleasure that I discovered that director Benny Chan&rsquo;s new film, <i>Connected</i>, is a remake of a Hollywood film &ndash; we&rsquo;ve finally struck back! But I was baffled all the same, by these people who went through all the trouble to officially remake the less-than-stellar Hollywood film <i>Cellular</i>. I mean, why would anyone throw money away on a script that didn&rsquo;t make much sense the first time around?</p>
<p>Answer: nobody has gone through much trouble, and nobody has thrown away much money, either. &ldquo;It&rsquo;s actually not me, but the investors, Warner China Film, who decided to make this Asian adaptation,&rdquo; explains Chan, one of the luminaries of Hong Kong action cinema. &ldquo;As Warner and New Line Cinema [of the US] are under the same company, it was quite easy to obtain the rights. In fact, the script had already been written up when they approached me. In that script, the story was changed into an investigation of a drug trafficking case, and it was more like a detective story... When I saw that, I decided to make it closer to the original version and adapt for a Hong Kong setting.&rdquo;</p>
<p>I can&rsquo;t help but be a little amused when Chan mentions that he has made effort to eliminate the plot holes in <i>Cellular</i> to make the whole thing more plausible. &ldquo;Of course it takes a little coincidence to make this story work,&rdquo; he adds. &ldquo;Remaking movies is not a bad thing in itself, as you can have a second chance to improve upon the original and put right its flaws.&rdquo;</p>
<p>For me, remaking movies is not a bad thing as long as the original is obscure enough. When asked if he is worried by the audience&rsquo;s familiarity with the original story, Chan says he isn&rsquo;t: &ldquo;as <i>Cellular</i> was not a major blockbuster in Hong Kong, the audience won&rsquo;t be too familiar with the story. In any case, I&rsquo;ve also included some action elements that aren&rsquo;t common in Hollywood films, like the car crashes in the new film.&rdquo; At least Chan has thought about how to make his film better than the original; Hollywood remakes are usually only concerned with making a fistful of dollars.</p>
<p>As the director of Hong Kong&rsquo;s first official remake of a Hollywood film, does Chan see the beginning of a new trend? &ldquo;I think this may only be a special case &ndash; it just so happens that a Hollywood script has attracted the attention of a mainland production company. At the end, it&rsquo;s really down to the investors.&rdquo; </p>
<p><i>Edmund Lee</i></p>
<i>Connected opens on Thu 25.</i>



</p>
EOF;
        // assert
        $this->assertEquals($expected, (string) $xml->review );
    }

} // class

?>
