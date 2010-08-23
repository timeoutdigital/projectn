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

} // class

?>
