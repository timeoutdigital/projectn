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
    ProjectN_Test_Unit_Factory::destroyDatabases();
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

  public function testEventsWithZeroAsRecurringListingIdAreNotSaved()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city'      => 'Lisbon',
      'language'  => 'pt',
      'time_zone' => 'Europe/Lisbon',
      )
    );

    $import = simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_xmllist.xml' );
    $placeids = $import->xpath( '/geral/listings/@placeid' );

    foreach( $placeids as $placeid )
    {
      $id = $placeid['placeid'];
      ProjectN_Test_Unit_Factory::add('poi', array("vendor_poi_id"=>$id));
    }

    $mapper = new LisbonFeedListingsMapper( $import );

    $importer = new Importer();
    $importer->addDataMapper( $mapper );
    $importer->run();

    $totalrecords = $import->xpath( '/geral/listings' );
    $notrecurring = $import->xpath( '/geral/listings[@RecurringListingID!=0]' );

    $this->assertNotEquals( count( $totalrecords ), count( $notrecurring ), "one" );

    $events = Doctrine::getTable( 'Event' )->count();

    $this->assertEquals( count( $notrecurring ), $events, "two" );
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

    // Although we have 14 days of Events, We will only Add events within 7 Days from Today.
    // hence, events occurrence should only have 2 Events in this case (Thursday & Friday as in XML Feed)
    $this->assertEquals(2,  $event['EventOccurrence']->count());
    $eventOccurrence1 = $event['EventOccurrence'][0];

    // This event occur Every Thursday & Friday of the week!
    if( date( 'l' ) != 'Thursday' && date( 'l' ) != 'Friday')
    {
        $this->assertEquals( date( 'Y-m-d', strtotime( 'next Thursday' ) ), $eventOccurrence1['start_date'] );
    }else
    {
        $this->assertEquals( date( 'Y-m-d' ), $eventOccurrence1['start_date'] );
    }

    $this->assertEquals( null, $eventOccurrence1['start_time'] );
    $this->assertEquals( '+01:00', $eventOccurrence1['utc_offset'] );

    // Check for Friday Event

    if( date( 'l' ) != 'Friday')
    {    //if today is not friday first occurrence should be next thursday and second should be friday
         $eventOccurrence2 = $event['EventOccurrence'][1];
         $this->assertEquals( date( 'Y-m-d', strtotime('next Friday') ), $eventOccurrence2['start_date'] );
    }else
    {
         //if today is Friday first occurrence should be todays
         $eventOccurrence2 = $event['EventOccurrence'][0];
         $this->assertEquals( date( 'Y-m-d' ), $eventOccurrence2['start_date'] );
    }


    $this->assertEquals( null, $eventOccurrence2['start_time'] );
    $this->assertEquals( '+01:00', $eventOccurrence2['utc_offset'] );

//    $eventOccurrences = Doctrine::getTable( 'EventOccurrence' )->findAll();
//    $this->assertEquals( 13, $eventOccurrences->count() );

//    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 50797 );
//    $this->assertEquals( 2 ,count( $event['EventOccurrence'] ), 'hooo'  );
//
//    $event = Doctrine::getTable( 'Event' )->createQuery( 'e' )->where( 'e.vendor_event_id = ?', 67337 )->andWhere( 'e.name = ?', 'Rozett 4 Tet')->fetchOne();
//    $this->assertEquals( 1 ,count( $event['EventOccurrence'] ), 'One event occurrence for Rozett 4 Tet'  );
//
//    $event = Doctrine::getTable( 'Event' )->createQuery( 'e' )->where( 'e.vendor_event_id = ?', 67337 )->andWhere( 'e.name = ?', 'Galamataki')->fetchOne();
//    $this->assertEquals( 2 ,count( $event['EventOccurrence'] ), 'Two event occurrences for Galamataki'  );


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
    $this->assertEquals($poi833Categories[1]['name'], 'Museus | Museus' );
    $this->assertEquals( $poi833Categories[2]['name'], 'Category | SubCategory' );
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

  public function testTimeInfoParsingToCreateOccurrences()
  {

    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' );

    // the event in the fixture we are trying to import might be expired so set a new date in future and also set t random musicid and recurring id

    $xml->listings[0]['ProposedFromDate'] = date( 'Y-m-d', strtotime( '-1 week' ) );
    $xml->listings[0]['ProposedToDate']   = date( 'Y-m-d', strtotime( '+1 week' ) );
    $xml->listings[0]['musicid'] =  555;
    $xml->listings[0]['RecurringListingID'] =  333;
    $xml->listings[0]['timeinfo'] =  "Ter-Sex 10-19h; Sab e Dom 14-19h. Encerra à 2ª feiras e feriados."; //should translate to Sunday,Tuesday,Friday,Saturday

    $importer = new Importer();

    $importer->addDataMapper( new LisbonFeedListingsMapper( $xml ) );

    $importer->run();

    $event  = Doctrine::getTable('Event')->findOneByVendorEventId( 333 );

    $validDays = array( 'Sunday','Tuesday','Friday','Saturday' );

    $occurrenceDates = array();
    $occurrenceDatesInDB = array();
    // find the occurrence dates
    for ($i=0 ; $i < 7; $i++ )
    {
        $day = date( 'l', strtotime( '+' .$i . ' day'  ) );
        if( in_array( $day, $validDays ) )
        {
            $occurrenceDates[] =date( 'Y-m-d' ,strtotime( '+' .$i . ' day'  ) );
        }
    }
    // get the occurrence dates in database
    foreach ( $event['EventOccurrence'] as $occurrence )
    {
        $occurrenceDatesInDB[] = $occurrence['start_date'];
    }

     $this->assertTrue(  count( array_diff( $occurrenceDates ,$occurrenceDatesInDB  ) ) == 0 , 'occurrence dates are valid' );
     $this->assertTrue(  count( array_diff( $occurrenceDatesInDB , $occurrenceDates  ) ) == 0 , 'occurrence dates are valid' );

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
    $geocoder = $this->getMock( 'geocoder' );
    $importer->addDataMapper( new LisbonFeedListingsMapper( $xml, $geocoder ) );
    //$importer->addLogger( $logger );
    $importer->run();
  }

}
?>
