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
    private $xmlString3 = '<rooter><parent><child id="5"><![CDATA[5]]></child></parent></rooter>';
    private $xmlString4 = '<rooter><dad><child id="6"><![CDATA[6]]></child></dad></rooter>';
    private $xmlString5 = '<root/>';

    public function setUp()
    {
        $this->xml1 = simplexml_load_string( $this->xmlString1 );
        $this->xml2 = simplexml_load_string( $this->xmlString2 );
        $this->xml3 = simplexml_load_string( $this->xmlString3 );
        $this->xml4 = simplexml_load_string( $this->xmlString4 );
        $this->xml5 = simplexml_load_string( $this->xmlString5 );
    }

    public function testConcatXML()
    {
        $concatXML = XmlConcatenator::concatXML( array( $this->xml1, $this->xml2, $this->xml3, $this->xml4, $this->xml5 ) );
     
        $this->assertEquals( 1, (string) $concatXML->parent[ 0 ]->child[ 0 ] );
        $this->assertEquals( 2, (string) $concatXML->parent[ 0 ]->child[ 1 ] );
        $this->assertEquals( 3, (string) $concatXML->parent[ 1 ]->child[ 0 ] );
        $this->assertEquals( 4, (string) $concatXML->parent[ 1 ]->child[ 1 ] );
        $this->assertEquals( 5, (string) $concatXML->parent[ 2 ]->child[ 0 ] );
        $this->assertEquals( 6, (string) $concatXML->dad[ 0 ]->child[ 0 ] );
    }

    public function testDefaultRootName()
    {
        $concatXML = XmlConcatenator::concatXML( array( $this->xml1, $this->xml2 ) );
        $this->assertEquals( 'root', (string) $concatXML->getName() );
    }

    public function testExplicitRootName()
    {
        $concatXML = XmlConcatenator::concatXML( array( $this->xml1, $this->xml2 ), 'CustomRoot' );
        $this->assertEquals( 'CustomRoot', (string) $concatXML->getName() );
    }

    public function testThrowInvalidXpathException()
    {
        $this->setExpectedException( 'XmlConcatenatorException', null, 'Invalid xpath' );
        $concatXML = XmlConcatenator::concatXML( array( $this->xml1, $this->xml2 ), 'roo/t' );
    }

    public function testThrowInvalidSimpleXMLException()
    {
        $this->setExpectedException( 'XmlConcatenatorException', null, 'No simplexml elements provided' );
        XmlConcatenator::concatXML( array(), '' );
    }
}