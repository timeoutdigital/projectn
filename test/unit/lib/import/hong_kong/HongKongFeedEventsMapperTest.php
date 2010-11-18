<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of Hong KOng Feed Events Mapper import.
 *
 * @package test
 * @subpackage hognkong.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class HongKongFeedEventsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var SimpleXMLElement
   */
  protected $eventsXml;

  /**
   * @var RussiaFeedEventsMapper
   */
  protected $dataMapper;

   /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    // get vendor
    $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'hong kong' );

    // Open event XMl and Update Event Occurrence Start dates to valid future dates, sot hat occurrences will be added everytime this test runs
    $this->eventsXml = simplexml_load_file( TO_TEST_DATA_PATH . '/hong_kong/hongkong_events.short.xml' );

    $tmpFilePath = $this->updateOccurrenceDateAndGetTmpFilePath();
    
    // params for new Mapper System
    $params = array( 'type' => 'test', 'datasource' => array( 'classname' => 'CurlMock', 'url' =>  $tmpFilePath) );

    $this->createVenuesFromVenueIds( $this->getVenueIdsFromXml() );

    $importer = new Importer();
    $importer->addDataMapper( new HongKongFeedEventsMapper( $this->vendor, $params ) );
    $importer->run();

    // remove the TMP file
    unlink( $tmpFilePath );
    
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

   public function testMapEvents()
  {

    $events = Doctrine::getTable('Event')->findAll();
    $this->assertEquals( 7, $events->count(), 'Should have same number of events imported as in the feed received.' );

    $event = $events[0]; // Get First to TEST
    
    $this->assertEquals( 35152,  $event['vendor_event_id'] );
    $this->assertEquals( 'Regular Race Afternoon Sha Tin', $event['name'] );
    $this->assertEquals( '', $event['short_description'] );
    $this->assertEquals( 'Regular race nights in Happy Valley.', $event['description'] );
    $this->assertEquals( 'http://www.racecourses.hkjc.com', $event['url'] );
    $this->assertEquals( 'Check website for info', $event['price'] );

    $this->assertEquals( "Timeout_link", $event[ 'EventProperty' ][0]['lookup'] ); // Timeout URL
    $this->assertEquals( "http://www.timeout.com.hk/around-town/events/35152/regular-race-afternoon-sha-tin.html", $event[ 'EventProperty' ][0]['value'] ); // Timeout URL


    $this->assertEquals( 'en-HK', $event->Vendor->language );
    $this->assertLessThan( 1, $event[ 'VendorEventCategory' ]->count(), 'No Category fir First' );

    $this->assertGreaterThan( 0, $event[ 'EventOccurrence' ]->count() );
    $this->assertEquals( '35152_2851_'.date( 'Ymd', strtotime( '+1 day' ) ).'000000', $event[ 'EventOccurrence' ][0]['vendor_event_occurrence_id'] );

    // Check for Other one with Category (Tag)
    $event = $events[3]; // Get 4nd to TEST
    $this->assertEquals( 35412,  $event['vendor_event_id'] );
    $this->assertEquals( 'Grafitti Night', $event['name'] );
    $this->assertEquals( '', $event['short_description'] );
    $this->assertEquals( '<p>Catch a live graffiti art show while you dance the night away. There', mb_substr($event['description'],0,71,'UTF-8') );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( 'Free (members), $250 incl. 2 drinks (non-members)', $event['price'] );

    $this->assertEquals( "Timeout_link", $event[ 'EventProperty' ][0]['lookup'] ); // Timeout URL
    $this->assertEquals( "http://www.timeout.com.hk/clubs/events/35412/grafitti-night.html", $event[ 'EventProperty' ][0]['value'] ); // Timeout URL

    $this->assertEquals( 'en-HK', $event->Vendor->language );

    $this->assertGreaterThan( 0, $event[ 'EventOccurrence' ]->count() );
    $this->assertEquals( '35412_5135_'.date( 'Ymd', strtotime( '+7 day' ) ).'000000', $event[ 'EventOccurrence' ][0]['vendor_event_occurrence_id'] );


    $this->assertEquals( "Clubs | Nightlife", $event[ 'VendorEventCategory' ][ 'Clubs | Nightlife' ][ 'name' ], 'Check event category' );

    
  }

  private function getVenueIdsFromXml()
  {
    $venues = $this->eventsXml->xpath('//venue_id');
    $venueIds = array();

    foreach( $venues as $venue )
      $venueIds[] = (string) $venue;

    $venueIds = array_unique( $venueIds );

    //make sure our keys are not missing a number
    sort( $venueIds );

    $this->assertEquals( 7, count( $venueIds ),
      'Should have 7 venues in the fixture, one for each Vendor.' );

    return $venueIds;
  }

  private function createVenuesFromVenueIds( $venueIds )
  {
    for( $i = 0; $i < count( $venueIds ); $i++ )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'vendor_poi_id' ] = $venueIds[ $i ];
      $poi[ 'Vendor' ] = $this->vendor;
      $poi->save();
    }
    $this->assertEquals( 7, Doctrine::getTable( 'Poi' )->count() );
  }

  private function updateOccurrenceDateAndGetTmpFilePath( )
  {
      $this->eventsXml->channel->events->event[0]->occurrences->occurrence[0]->start_date = date('Y-m-d', strtotime( '+1 day' ) );
      $this->eventsXml->channel->events->event[1]->occurrences->occurrence[0]->start_date = date('Y-m-d', strtotime( '+2 day' ) );
      $this->eventsXml->channel->events->event[3]->occurrences->occurrence[0]->start_date = date('Y-m-d', strtotime( '+7 day' ) );

      // create a Temporary file///
      $tmpfile = tempnam( '/tmp', 'xml');

      // save the XML
      file_put_contents( $tmpfile , $this->eventsXml->saveXML() );

      return $tmpfile;
  }
  
}

?>