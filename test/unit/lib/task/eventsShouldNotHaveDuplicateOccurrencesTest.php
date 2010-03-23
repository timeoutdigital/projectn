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
class eventsShouldNotHaveDuplicateOccurrencesTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $goodTask = new eventsShouldNotHaveDuplicateOccurrencesTestTask( true );
    $test     = new eventsShouldNotHaveDuplicateOccurrences(  );
    $this->assertTrue( $test->run( $goodTask ) );

    $badTask = new eventsShouldNotHaveDuplicateOccurrencesTestTask( false );
    $test    = new eventsShouldNotHaveDuplicateOccurrences( );
    $this->assertFalse( $test->run( $badTask ) );
  }
}

class eventsShouldNotHaveDuplicateOccurrencesTestTask
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
        <event>
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
        <event>
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
            <place place-id="SIN000000000000000000000000000088">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
        <event>
          <name>baz kee</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-19</start_date>
                  <event_time>00:00</event_time>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-19</start_date>
                  <event_time>00:01</event_time>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
        <event>
          <name>baz kee</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-19</start_date>
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
        <event>
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
        <event>
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
            <place place-id="SIN000000000000000000000000000088">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
          </showtimes>
        </event>
        <event>
          <name>baz kee</name>
          <showtimes>
            <place place-id="SIN000000000000000000000000000078">
              <occurrence>
                <time>
                  <start_date>2010-03-18</start_date>
                  <utc_offset>+00:00</utc_offset>
                </time>
              </occurrence>
            </place>
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
