<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class XmlConcatenatorTest extends PHPUnit_Framework_TestCase
{
    public function testConcatXML()
    {
        $xml1 = simplexml_load_string( '<root><parent><child id="1"><![CDATA[1]]></child><child id="2"><![CDATA[2]]></child></parent></root>' );
        $xml2 = simplexml_load_string( '<root><parent><child id="3"><![CDATA[3]]></child><child id="4"><![CDATA[4]]></child></parent></root>' );

        $this->assertEquals( 1, (string) $xml1->parent->child[ 0 ] );
        $this->assertEquals( 2, (string) $xml1->parent->child[ 1 ] );
        
        $this->assertEquals( 3, (string) $xml2->parent->child[ 0 ] );
        $this->assertEquals( 4, (string) $xml2->parent->child[ 1 ] );

        $concatXML = XmlConcatenator::concatXML( array( $xml1, $xml2 ), '//parent/child' );

        $this->assertEquals( 1, (string) $concatXML->parent->child[ 0 ] );
        $this->assertEquals( 2, (string) $concatXML->parent->child[ 1 ] );
        $this->assertEquals( 3, (string) $concatXML->parent->child[ 2 ] );
        $this->assertEquals( 4, (string) $concatXML->parent->child[ 3 ] );
    }
}