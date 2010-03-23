<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for the eventsShouldNotHaveDuplicateOccurrences
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
class noDuplicateEventsTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $goodTask = new noDuplicateEventsTestTask( true );
    $test     = new noDuplicateEvents();
    $this->assertTrue( $test->run( $goodTask ) );

    $badTask = new noDuplicateEventsTestTask( false );
    $test     = new noDuplicateEvents();
    $this->assertFalse( $test->run( $badTask ) );
  }
}

class noDuplicateEventsTestTask
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

  public function getPoiXml() { }

  public function getEventXml()
  {
    if( $this->good )
    {
      return simplexml_load_string( '
      <root>
        <event id="SIN000000000000000000000000000100">
          <name>foo</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
        <event id="SIN000000000000000000000000000101">
          <name>bar</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
      </root>
      ' );
    }
    else
    {
      return simplexml_load_string( '
      <root>
        <event id="SIN000000000000000000000000000100">
          <name>foo</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
        <event id="SIN000000000000000000000000000100">
          <name>bar</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
      </root>
      ' );
    }
  }
}
