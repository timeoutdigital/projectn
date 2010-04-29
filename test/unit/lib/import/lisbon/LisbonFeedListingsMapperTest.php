<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LisbonFeedListingsMapper.
 *
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

    foreach( array( 833, 2844/*, 4109*/ ) as $placeid )
    {
      ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => $placeid ) );
    }

    $this->object = new LisbonFeedListingsMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' )
    );
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
   * Test to make sure 'start_time' property is present where time_info has start time info.
   */
  public function testStartTimePropertyExistsWhereTimeInfoIsPresentAndContainsStartTime()
  {
    $this->markTestSkipped();
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $search = Doctrine::getTable('Event')->findAll();
    foreach( $search as $event )
    {
        foreach( $event['EventProperty'] as $ep )
        {
            if( $e['lookup'] = 'timeinfo' )
            {
                
            }
        }
    }

    print_r( $search->toArray() );
  }

  /**
   * Test to make sure 'band' property is not present.
   * refs #259
   */
  public function testBandPropertyIsNotPresent()
  {
    $this->markTestSkipped();
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
    $this->markTestSkipped();
    $this->markTestSkipped();
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

    $events = Doctrine::getTable( 'EventOccurrence' )->count();

    $this->assertEquals( count( $notrecurring ), $events, "two" );
  }

  /**
   * @todo Implement testMapVenues().
   */
  public function testMapListings()
  {
    $this->markTestSkipped();
    $importer = new Importer();
    //$importer->addLogger( new echoingLogger() );
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();
    $this->assertEquals( 3, $events->count() );

    $event = $events[0];

    $this->assertEquals( '50805', $event['vendor_event_id'] );
    $this->assertEquals( 'Arquitecto José Santa-Rita, arquitecto: Obra, marcas e identidade(s) de um percu', $event['name'] );
    $this->assertEquals( 'Constituindo, pela primeira vez, uma homenagem póstuma, esta edição do Prémio Mu', $event['short_description'] );
    $this->assertRegExp( '/^Constituindo,.*Exposições.$/', $event['description'] );
    $this->assertEquals( '', $event['booking_url'] );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( '', $event['price'] );
    $this->assertEquals( '', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    $eventOccurrence1 = $event['EventOccurrence'][0];
    $this->assertEquals( '2010-01-01', $eventOccurrence1['start_date'] );
    $this->assertEquals( null, $eventOccurrence1['start_time'] );
    $this->assertEquals( '+00:00', $eventOccurrence1['utc_offset'] );

    $eventOccurrence2 = $event['EventOccurrence'][1];
    $this->assertEquals( '2010-07-07', $eventOccurrence2['start_date'] );
    $this->assertEquals( null, $eventOccurrence2['start_time'] );
    $this->assertEquals( '+01:00', $eventOccurrence2['utc_offset'] );

    $eventOccurrences = Doctrine::getTable( 'EventOccurrence' )->findAll();
    $this->assertEquals( 6, $eventOccurrences->count() );
    
    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 50797 );
     
    $this->assertEquals( 2 ,count( $event['EventOccurrence'] ), 'hooo'  );
  }

  public function testAddsCategoryToPoi()
  {
    $this->markTestSkipped();
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $poi833Results = Doctrine::getTable('Poi')->findByVendorPoiId( 833 );
    $this->assertEquals( 1, $poi833Results->count() );

    $poi833 = $poi833Results[0];
    $poi833Categories = $poi833['VendorPoiCategory'];
    //var_dump( $poi833['VendorPoiCategory']->toArray() );
    $this->assertEquals( 3, $poi833Categories->count() );
    $this->assertEquals( $poi833Categories[0]['name'], 'test name' );//autocreated by bootstrap
    $this->assertEquals( $poi833Categories[1]['name'], 'Museus | Museus' );
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

  private function addPoi( $name, $vendor )
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi['poi_name'] = $vendor;
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
    $geocoder = $this->getMock( 'geoEncode' );
    $importer->addDataMapper( new LisbonFeedListingsMapper( $xml, $geocoder ) );
    $importer->addLogger( $logger );
    $importer->run();
  }
}
?>
