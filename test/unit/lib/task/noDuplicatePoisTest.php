<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for the poisShouldNotHaveDuplicateOccurrences
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class noDuplicatePoisTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $goodTask = new noDuplicatePoisTestTask( true );
    $test     = new noDuplicatePois();
    $this->assertTrue( $test->run( $goodTask ) );

    $badTask = new noDuplicatePoisTestTask( false );
    $test     = new noDuplicatePois();
    $this->assertFalse( $test->run( $badTask ) );
  }
}

class noDuplicatePoisTestTask
{
  private $good;
  private $fileNames = array(
    'poi-xml' => 'poi.xml',
  );

  public function __construct( $good = true )
  {
    $this->good = $good;
  }

  public function getOption( $option )
  {
    return $this->fileNames[ $option ];
  }

  public function getPoiXml()
  {
    if( $this->good )
    {
      return simplexml_load_string( '
        <root>
          <entry vpid="SIN00000000000000001234" lang="en" modified="2010-03-12T17:18:44">
            <geo-position>
              <longitude>103.849900000000000</longitude>
              <latitude>1.289406000000000</latitude>
            </geo-position>
            <name><![CDATA[Aseana Gallery]]></name>
            <category><![CDATA[restaurant]]></category>
            <address>
              <street><![CDATA[Riverside Point, 30 Merchant Rd]]></street>
              <city><![CDATA[Singapore]]></city>
              <country>SGP</country>
            </address>
          </entry>
          <entry vpid="SIN00000000000000001235" lang="en" modified="2010-03-12T17:18:44">
            <geo-position>
              <longitude>103.849900000000000</longitude>
              <latitude>1.289406000000000</latitude>
            </geo-position>
            <name><![CDATA[Test]]></name>
            <category><![CDATA[restaurant]]></category>
            <address>
              <street><![CDATA[Riverside Point, 30 Merchant Rd]]></street>
              <city><![CDATA[Singapore]]></city>
              <country>SGP</country>
            </address>
          </entry>
        </root>
      ' );
    }
    else
    {
      return simplexml_load_string( '
        <root>
          <entry vpid="SIN00000000000000001234" lang="en" modified="2010-03-12T17:18:44">
            <geo-position>
              <longitude>103.849900000000000</longitude>
              <latitude>1.289406000000000</latitude>
            </geo-position>
            <name><![CDATA[Aseana Gallery]]></name>
            <category><![CDATA[restaurant]]></category>
            <address>
              <street><![CDATA[Riverside Point, 30 Merchant Rd]]></street>
              <city><![CDATA[Singapore]]></city>
              <country>SGP</country>
            </address>
          </entry>
          <entry vpid="SIN00000000000000001234" lang="en" modified="2010-03-12T17:18:44">
            <geo-position>
              <longitude>103.849900000000000</longitude>
              <latitude>1.289406000000000</latitude>
            </geo-position>
            <name><![CDATA[Aseana Gallery]]></name>
            <category><![CDATA[restaurant]]></category>
            <address>
              <street><![CDATA[Riverside Point, 30 Merchant Rd]]></street>
              <city><![CDATA[Singapore]]></city>
              <country>SGP</country>
            </address>
          </entry>
        </root>
      ' );
    }
  }

  public function getEventXml() { }
}
