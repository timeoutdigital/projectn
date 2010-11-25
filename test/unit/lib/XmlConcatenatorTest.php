<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * XmlConcatenatorTest
 *
 * @package projectn
 * @subpackage unit
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 * @version 1.0.0
 */

class XmlConcatenatorTest extends PHPUnit_Framework_TestCase
{
    private $xmlString1 = '<root><parent><child id="1"><![CDATA[1]]></child><child id="2"><![CDATA[2]]></child></parent></root>';
    private $xmlString2 = '<root><parent><child id="3"><![CDATA[3]]></child><child id="4"><![CDATA[4]]></child></parent></root>';

    public function setUp()
    {
        $this->xml1 = simplexml_load_string( $this->xmlString1 );
        $this->xml2 = simplexml_load_string( $this->xmlString2 );
    }

    public function testConcatXML()
    {
        $this->assertEquals( 1, (string) $this->xml1->parent->child[ 0 ] );
        $this->assertEquals( 2, (string) $this->xml1->parent->child[ 1 ] );
        
        $this->assertEquals( 3, (string) $this->xml2->parent->child[ 0 ] );
        $this->assertEquals( 4, (string) $this->xml2->parent->child[ 1 ] );

        $concatXML = XmlConcatenator::concatXML( array( $this->xml1, $this->xml2 ), '//parent/child' );

        $this->assertEquals( 1, (string) $concatXML->parent->child[ 0 ] );
        $this->assertEquals( 2, (string) $concatXML->parent->child[ 1 ] );
        $this->assertEquals( 3, (string) $concatXML->parent->child[ 2 ] );
        $this->assertEquals( 4, (string) $concatXML->parent->child[ 3 ] );
    }

    public function testThrowInvalidXpathException()
    {
        $this->setExpectedException( 'XmlConcatenatorException', null, 'Invalid xpath' );
        XmlConcatenator::concatXML( array( $this->xml1, $this->xml2 ), '' );
    }

    public function testThrowInvalidSimpleXMLException()
    {
        $this->setExpectedException( 'XmlConcatenatorException', null, 'No simplexml elements provided' );
        XmlConcatenator::concatXML( array(), '' );
    }
}