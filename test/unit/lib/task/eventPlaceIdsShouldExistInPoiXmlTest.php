<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for the eventPlaceIdsShouldExistInPoiXml
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class eventPlaceIdsShouldExistInPoiXmlTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $goodTask = new eventPlaceIdsShouldExistInPoiXmlTestTask( true );
    $test = new eventPlaceIdsShouldExistInPoiXml( $goodTask );
    $this->assertTrue( $test->run() );

    $badTask = new eventPlaceIdsShouldExistInPoiXmlTestTask( false );
    $test = new eventPlaceIdsShouldExistInPoiXml( $badTask );
    $this->assertFalse( $test->run() );
  }
}

class eventPlaceIdsShouldExistInPoiXmlTestTask
{
  private $good;
  private $fileNames = array(
    'event-xml' => 'event.xml',
    'poi-xml'   => 'poi.xml',
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
    return simplexml_load_string( '
    <root>
      <entry vpid="1"/>
      <entry vpid="2"/>
      <entry vpid="3"/>
    </root>
    ' );
  }

  public function getEventXml()
  {
    if( $this->good )
    {
      return simplexml_load_string( '
      <root>
        <event>
          <showtimes>
            <place place-id="1"/>
          </showtimes>
        </event>
        <event>
          <showtimes>
            <place place-id="2"/>
          </showtimes>
        </event>
        <event>
          <showtimes>
            <place place-id="3"/>
          </showtimes>
        </event>
      </root>
      ' );
    }
    else
    {
    }
      return simplexml_load_string( '
      <root>
        <event>
          <showtimes>
            <place place-id="5"/>
          </showtimes>
        </event>
        <event>
          <showtimes>
            <place place-id="6"/>
          </showtimes>
        </event>
        <event>
          <showtimes>
            <place place-id="7"/>
          </showtimes>
        </event>
      </root>
      ' );
  }
}
