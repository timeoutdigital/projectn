<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LisbonFeedListingsMapper.
 * Lisbon Gives event date range and the days of event occurrences, ]
 * Mapper will only add events for next 7 days. Event dates are specified as #ProposedFromDate# = #ProposedToDate#
 * and days of the event occurrences are mentioned in the #timeinfo# as text
 *
 * @see There is some prebuilt logic in the repository (commits of this class before the 23/11/2010) to parse
 *      the Lisbon timinfo string field and build occurrences out of it
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LisbonFeedListingsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedListingsMapper
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city'      => 'Lisbon',
      'language'  => 'pt',
      'time_zone' => 'Europe/Lisbon',
      )
    );
    $this->vendor = $vendor;

    ImportLogger::getInstance()->enabled( false );

    foreach( array( 833, 2844, 366, 4109 ) as $placeid )
    {
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => $placeid ) );
    }

    // Load XML
    $xmlData =  simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' );

    // Simulate Dynamic Dates to test in future
    // updating dates for First two nodes in the feed
    $xmlData->listings[0]['ProposedFromDate'] = $xmlData->listings[1]['ProposedFromDate'] = date('Y-m-d\T00:00:00');
    $xmlData->listings[0]['ProposedToDate'] = $xmlData->listings[1]['ProposedToDate'] = date('Y-m-d\T00:00:00', mktime(0,0,0, date('m'), date('d')+14, date('Y')));

    $this->object = new LisbonFeedListingsMapper( $xmlData );

  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
     ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Test to make sure Question Marks are replaced with Euro Signs
   */
  public function testEuroSignsInsteadOfQuestionMarks()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable('Event')->findAll();
    foreach( $events as $event )
        $this->assertEquals( false, strpos( $event['price'], "?" ), "Price should not contain a question mark." );
  }

  /**
   * Test to make sure 'start_time' property is present where time_info has start time info.
   */
  public function testDescriptionHtmlIsNotWrittenWithPerentheses()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $search = Doctrine::getTable('Event')->findAll();

    $this->assertEquals( false, strpos( $search[0]['description'], "{I}Robinson Crusoe{/I}" ), "Description cannot contain parenthesis for html elements." );
    $this->assertNotEquals( false, strpos( $search[0]['description'], "<I>Robinson Crusoe</I>" ), "Description cannot contain parenthesis for html elements." );
  }

  /**
   * Test to make sure 'band' property is not present.
   * refs #259
   */
  public function testBandPropertyIsNotPresent()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $search = Doctrine::getTable('Event')->findById( 1 );
    $first = $search->getFirst();

    foreach( $first['EventProperty'] as $eventProperty )
    {
        $this->assertNotEquals( $eventProperty['lookup'], 'band', "Event should not have property named 'band', refs #259" );
    }
  }

  public function testDifferentEventTypesAreSavedCorrectly()
  {
    $import = simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_xmllist.xml' );
    $placeids = $import->xpath( '/geral/listings/@placeid' );

    foreach( $placeids as $placeid )
    {
      $id = (integer) $placeid['placeid'];
      ProjectN_Test_Unit_Factory::add('poi', array( "vendor_poi_id" => $id ) );
    }

    $mapper = new LisbonFeedListingsMapper( $import );

    $importer = new Importer();
    $importer->addDataMapper( $mapper );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();

    $this->assertEquals( 5, $events->count(), "failed to import all events" );

    $this->assertEquals( 1, $events[0]['EventOccurrence']->count(), "wrong number of occurrences" );
    $this->assertEquals( 1, $events[1]['EventOccurrence']->count(), "wrong number of occurrences" );
    $this->assertEquals( 2, $events[2]['EventOccurrence']->count(), "wrong number of occurrences" );
    $this->assertEquals( 1, $events[3]['EventOccurrence']->count(), "wrong number of occurrences" );
    $this->assertEquals( 1, $events[4]['EventOccurrence']->count(), "wrong number of occurrences" );

    $this->assertEquals( 36436, $events[0]['vendor_event_id'], "wrong number of occurrences" );

    $this->assertEquals( 36436, $events[0]['vendor_event_id'], "wrong event id" );
    $this->assertEquals( 177367, $events[0]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong occurrence id" );
    $this->assertEquals( 1, count( $events[0]['EventOccurrence']) );

    $this->assertEquals( 50397, $events[1]['vendor_event_id'], "wrong number of occurrences" );
    $this->assertEquals( 177727, $events[1]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[1]['EventOccurrence']) );

    $this->assertEquals( 45934, $events[2]['vendor_event_id'], "wrong number of occurrences" );
    $this->assertEquals( 178086, $events[2]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 178087, $events[2]['EventOccurrence'][1]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 2, count( $events[2]['EventOccurrence']) );

    /* the vendor_event_id and vendor_event_occurrence_id are equal for the non recurring events */

    $this->assertEquals( 178290, $events[3]['vendor_event_id'], "wrong number of occurrences" );
    $this->assertEquals( 178290, $events[3]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[3]['EventOccurrence']) );

    $this->assertEquals( 178291, $events[4]['vendor_event_id'], "wrong number of occurrences" );
    $this->assertEquals( 178291, $events[4]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[4]['EventOccurrence']) );
  }

  /**
   * @todo Implement testMapVenues().
   */
  public function testMapListings()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();

    $this->assertEquals( 8, $events->count() );

    $event = $events[0];

    $this->assertEquals( '50805', $event['vendor_event_id'] );
    $this->assertEquals( 'Arquitecto José Santa-Rita, arquitecto: Obra, marcas e identidade(s) de um percu', $event['name'] );
    $this->assertEquals( 'Constituindo, pela primeira vez, uma homenagem póstuma, esta edição do Prémio Mu', $event['short_description'] );
    $this->assertRegExp( '/^Patrcia.*percurso$/', $event['description'] );
    $this->assertEquals( '', $event['booking_url'] );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( '', $event['price'] );
    $this->assertEquals( '', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    $this->assertEquals(2,  $event['EventOccurrence']->count());
    $eventOccurrence1 = $event['EventOccurrence'][0];
    $this->assertEquals( '2010-01-01', $eventOccurrence1['start_date'] );
    $this->assertEquals( null, $eventOccurrence1['start_time'] );
    $this->assertEquals( $this->getUtcOffsetForDate( '2010-01-01' ), $eventOccurrence1['utc_offset'] );

    $eventOccurrence2 = $event['EventOccurrence'][1];
    $this->assertEquals( '2010-11-19', $eventOccurrence2['start_date'] );
    $this->assertEquals( null, $eventOccurrence2['start_time'] );
    $this->assertEquals( $this->getUtcOffsetForDate( '2010-11-19' ), $eventOccurrence2['utc_offset'] );

    $eventOccurrences = Doctrine::getTable( 'EventOccurrence' )->findAll();
    $this->assertEquals( 13, $eventOccurrences->count() );

    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 50797 );
    $this->assertEquals( 2 ,count( $event['EventOccurrence'] ), 'hooo'  );

    $event = Doctrine::getTable( 'Event' )->createQuery( 'e' )->where( 'e.vendor_event_id = ?', 67337 )->andWhere( 'e.name = ?', 'Rozett 4 Tet')->fetchOne();
    $this->assertEquals( 3 ,count( $event['EventOccurrence'] ), 'One event occurrence for Rozett 4 Tet'  );
  }

  public function testAddsCategoryToPoi()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $poi833Results = Doctrine::getTable('Poi')->findByVendorPoiId( 833 );

    $this->assertEquals( 1, $poi833Results->count() );

    $poi833 = $poi833Results[0];

    $poi833Categories = $poi833['VendorPoiCategory'];

    $this->assertEquals( 2, $poi833Categories->count() );

    $this->assertEquals( $poi833Categories[0]['name'], 'test name' );//autocreated by bootstrap
    $this->assertEquals($poi833Categories[1]['name'], 'Museus | Museus' );
    //$this->assertEquals( $poi833Categories[2]['name'], 'Category | SubCategory' );
  }

  public function testIsNotAffectedByEventsFromOtherVendors()
  {
    $lisbon    = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city'=>'lisbon', 'language'=>'pt' ) );
    $lisbonPoi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'placeid' => 1 ) ); //fixture event happens at this event

    $london    = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city'=>'london', 'language'=>'en-GB' ) );
    $londonPoi = $this->addPoi( 'London Poi', $london );
    $this->addEvent( 'Cool Event', $londonPoi, $london );

    $logger = $this->getMock('doNothingLogger', array( 'addError' ) );
    $logger->expects( $this->exactly( 0 ) )
           ->method( 'addError' )
           ;

    //fixture has an event with name 'Cool Event'
    $this->importFrom(TO_TEST_DATA_PATH . '/lisbon_listings_testIsNotAffectedByEventsFromOtherVendors.xml', $logger);
  }

  private function addPoi( $name, $vendor )
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi['poi_name'] = $name;
    $poi['Vendor'] = $vendor;
    $poi->save();
  }

  private function addEvent( $name, $poi, $vendor )
  {
    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event['name']   = $name;
    $event['Vendor'] = $vendor;
    $event->save();
  }

  private function importFrom( $file, $logger )
  {
    $importer = new importer();
    $xml = simplexml_load_file( $file );
    $geocoder = $this->getMock( 'googleGeocoder' );
    $importer->addDataMapper( new LisbonFeedListingsMapper( $xml, $geocoder ) );
    //$importer->addLogger( $logger );
    $importer->run();
  }

  private function getUtcOffsetForDate( $date )
  {
    $timezoneLisbon = new DateTimeZone( 'Europe/Lisbon' );
    $dateTimeLisbon = new DateTime( $date, $timezoneLisbon ) ;
    return $dateTimeLisbon->format( 'P' );
  }

}
?>
