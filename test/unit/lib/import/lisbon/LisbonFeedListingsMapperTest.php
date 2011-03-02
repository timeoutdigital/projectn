<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test class for LisbonFeedListingsMapper.
 * Lisbon Gives event date range and the days of event occurrences
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
   * @var SimpleXMLElement $xmlData
   */
  protected $xmlData;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    ImportLogger::getInstance()->enabled( false );

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city'      => 'Lisbon',
      'language'  => 'pt',
      'time_zone' => 'Europe/Lisbon',
      )
    );

    $params = array(
        'type' => 'poi',
        'curl' => array(
            'classname' => 'CurlMock',
            'src' => TO_TEST_DATA_PATH . '/lisbon_listings.short.xml'
        )
    );

    foreach( array( 833, 2844, 366, 4109 ) as $placeid )
    {
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => $placeid ) );
    }

    $this->object = new LisbonFeedListingsMapper( $this->vendor, $params );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
     ProjectN_Test_Unit_Factory::destroyDatabases();
  }


  public function testSortSimpleXmlByAttribute()
  {
      $sortedXml = $this->object->getXml();

      //order of target xml
      $this->assertEquals(  '0', (string) $sortedXml->listings[0]['RecurringListingID']  );
      $this->assertEquals(  '0', (string) $sortedXml->listings[1]['RecurringListingID']  );
      $this->assertEquals(  '0', (string) $sortedXml->listings[2]['RecurringListingID']  );
      $this->assertEquals(  '234', (string) $sortedXml->listings[3]['RecurringListingID']  );
      $this->assertEquals(  '50699', (string) $sortedXml->listings[4]['RecurringListingID']  );
      $this->assertEquals(  '50797', (string) $sortedXml->listings[5]['RecurringListingID']  );
      $this->assertEquals(  '50797', (string) $sortedXml->listings[6]['RecurringListingID']  );
      $this->assertEquals(  '50805', (string) $sortedXml->listings[7]['RecurringListingID']  );
      $this->assertEquals(  '50805', (string) $sortedXml->listings[8]['RecurringListingID']  );
      $this->assertEquals(  '67337', (string) $sortedXml->listings[9]['RecurringListingID']  );
      $this->assertEquals(  '67337', (string) $sortedXml->listings[10]['RecurringListingID']  );
      $this->assertEquals(  '67337', (string) $sortedXml->listings[11]['RecurringListingID']  );
      $this->assertEquals(  '6887688', (string) $sortedXml->listings[12]['RecurringListingID']  );
      $this->assertEquals(  '6887689', (string) $sortedXml->listings[13]['RecurringListingID']  );
      $this->assertEquals(  '65876870', (string) $sortedXml->listings[14]['RecurringListingID'], 'if this fails the sort is probably not numeric'  );

      //sample of target xml attributes
      $this->assertEquals(  'Nuestros Silencios', (string) $sortedXml->listings[3]['gigKey']  );
      $this->assertEquals(  '1672342', (string) $sortedXml->listings[3]['musicid']  );
      $this->assertEquals(  '234', (string) $sortedXml->listings[3]['RecurringListingID']  );
      $this->assertEquals(  '2010-01-02T00:00:00', (string) $sortedXml->listings[3]['ListingDate']  );
      $this->assertEquals(  'Praça Marquês de Pombal ', (string) $sortedXml->listings[3]['place']  );
      $this->assertEquals(  'In Progress', (string) $sortedXml->listings[3]['listingstatus']  );
      $this->assertEquals(  '', (string) $sortedXml->listings[3]['image']  );

      //sample of target xml elements (children and text nodes)
      $this->assertTrue( isset( $sortedXml->listings[4]->testnode->testsubnode->testtextnode ) );
      $this->assertEquals(  'asdfasdf', trim ( (string) $sortedXml->listings[4]->testnode->testsubnode->testtextnode )  );
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

    $this->assertEquals( false, strpos( $search[6]['description'], "{I}Robinson Crusoe{/I}" ), "Description cannot contain parenthesis for html elements." );
    $this->assertNotEquals( false, strpos( $search[6]['description'], "<I>Robinson Crusoe</I>" ), "Description cannot contain parenthesis for html elements." );
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
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();

    $this->assertEquals( 10, $events->count(), "failed to import all events" );

    /* test a recurring events */
    $this->assertEquals( 67337, $events[7]['vendor_event_id'], "wrong event id" );
    $this->assertEquals( 223406, $events[7]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 223397, $events[7]['EventOccurrence'][1]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 223398, $events[7]['EventOccurrence'][2]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 3, count( $events[7]['EventOccurrence']) );

    /* test non recurring events */
    $this->assertEquals( 100, $events[0]['vendor_event_id'], "wrong event id" );
    $this->assertEquals( 100, $events[0]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[0]['EventOccurrence']) );

    $this->assertEquals( 101, $events[1]['vendor_event_id'], "wrong event id" );
    $this->assertEquals( 101, $events[1]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[1]['EventOccurrence']) );

    $this->assertEquals( 102, $events[2]['vendor_event_id'], "wrong event id" );
    $this->assertEquals( 102, $events[2]['EventOccurrence'][0]['vendor_event_occurrence_id'], "wrong number of occurrences" );
    $this->assertEquals( 1, count( $events[2]['EventOccurrence']) );
  }

  /**
   * @todo Implement testMapVenues().
   */
  public function testEventMapListings()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();

    $event = $events[6];

    $this->assertEquals( '50805', $event['vendor_event_id'] );
    $this->assertEquals( 'Arquitecto José Santa-Rita, arquitecto: Obra, marcas e identidade(s) de um percu', $event['name'] );
    $this->assertEquals( 'Constituindo, pela primeira vez, uma homenagem póstuma, esta edição do Prémio Mu', $event['short_description'] );
    $this->assertRegExp( '/^Patrcia.*percurso$/', $event['description'] );
    $this->assertEquals( '', $event['booking_url'] );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( '', $event['price'] );
    $this->assertEquals( '', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    /*test that timeinfo property exists*/
    $this->assertEquals( 'timeinfo', $event['EventProperty'][0]['lookup'], 'timeinfo property not available' );
    $this->assertEquals( 'Ter-Sex 10-19h; Sab e Dom 14-19h. Encerra à 2ª feiras e feriados.', $event['EventProperty'][0]['value'], 'timeinfo property does not match expected value' );

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


  /**
   * testEventMapListingsWithDifferentContentPerEvent()
   * 
   * makes sure that event fields get 'nulled' if they are inconsistent over the
   * course of muliple occurrences
   */
  public function testEventMapListingsWithDifferentContentPerEvent()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();

    $event = $events[5];

    $this->assertEquals( '50797', $event['vendor_event_id'] );
    /*data should be present*/
    $this->assertEquals( 'Nuestros Silencios', $event['name'] );
    $this->assertEquals( 'Lisboa é a primeira cidade europeia a acolher a exposição de arte pública do esc', $event['short_description'] );
    /*data in field should be removed*/
    $this->assertEquals( '', $event['description'] );

    /*check properties that timeinfo property does not exist*/
    $this->assertEquals( 0, count( $event['EventProperty'] ), 'unexpected timeinfo property found' );
  }


  public function testThatEventWithDifferentNamesIsNotInDatabase()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( '65876870' );

    $this->assertFalse( $event instanceof Event );
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

    $this->assertEquals( 3, $poi833Categories->count() );

    $this->assertEquals( $poi833Categories[0]['name'], 'test name' );//autocreated by bootstrap
    $this->assertEquals($poi833Categories[1]['name'], 'Museus' );
    $this->assertEquals( $poi833Categories[2]['name'], 'Category | SubCategory' );
  }

  public function testIsNotAffectedByEventsFromOtherVendors()
  {
    $this->markTestIncomplete( 'Legacy Code. Please complete this test & refactor where possible.' );

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
