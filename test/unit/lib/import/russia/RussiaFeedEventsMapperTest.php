<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Russia Feed Events Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class RussiaFeedEventsMapperTest extends PHPUnit_Framework_TestCase
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
    $this->addRussianVendors();
    $this->eventsXml = simplexml_load_file( TO_TEST_DATA_PATH . '/russia_events.short.xml' );

    $this->dataMapper = new RussiaFeedEventsMapper( $this->eventsXml, null, "moscow" );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testImportsEventsToCorrectVendor()
  {
    $this->createVenuesFromVenueIds( $this->getVenueIdsFromXml() );

    $importer = new Importer();
    $importer->addDataMapper( $this->dataMapper );
    $importer->run();

    $this->assertEquals( 10, Doctrine::getTable( 'Event' )->count(), 'Should have 10 events in total' );

    $russianVendor1 = Doctrine::getTable( 'Vendor' )->findOneById( 1 );
    $this->assertEquals( 1, $russianVendor1['Event']->count(), $russianVendor1['city'] . ' should have one Event.' );

    $russianVendor2 = Doctrine::getTable( 'Vendor' )->findOneById( 2 );
    $this->assertEquals( 2, $russianVendor2['Event']->count(), $russianVendor2['city'] . ' should have one Event.' );

    $russianVendor3 = Doctrine::getTable( 'Vendor' )->findOneById( 3 );
    $this->assertEquals( 3, $russianVendor3['Event']->count(), $russianVendor3['city'] . ' should have one Event.' );
  }

  public function testMapEvents()
  {
    $this->createVenuesFromVenueIds( $this->getVenueIdsFromXml() );

    $importer = new Importer();
    $importer->addDataMapper( $this->dataMapper );
    $importer->run();

    $events = Doctrine::getTable('Event')->findAll();
    $this->assertEquals( 10, $events->count(), 'Should have same number of events imported as in the feed received.' );
    $event = $events[0];

    $this->assertEquals( '2008-10-27', $event['review_date'] );
    $this->assertEquals( 373,  $event['vendor_event_id'] );
    $this->assertEquals( 'Двое на качелях', $event['name'] );
    $this->assertEquals( 'Спектакль о двух одиноких людях, потерявших опору в жизни. Отношения их складываются непросто, но нахлынувшее чувство позволяет вновь обрести вкус к жизни.', $event['short_description'] );
    $this->assertEquals( 'Спектакль', mb_substr( $event['description'], 0, 9, "UTF-8" ) );
    
    $this->assertEquals( 'ru', $event->Vendor->language );
    
    $this->assertGreaterThan( 0, $event[ 'VendorEventCategory' ]->count(), 'Should have more that one VendorEventCategory' );
    $this->assertEquals( "Драма | Театр", $event[ 'VendorEventCategory' ][ 'Драма | Театр' ][ 'name' ], 'Check event category' );

    $this->assertGreaterThan( 0, $event[ 'EventMedia' ]->count(), 'Should have more than one media' );
    $this->assertEquals( "http://pix.timeout.ru/63530.jpeg", $event[ 'EventMedia' ][0]['url'], 'Check media url' );

    $this->assertGreaterThan( 0, $event[ 'EventOccurrence' ]->count() );
    $this->assertEquals( 4630349, $event[ 'EventOccurrence' ][0]['vendor_event_occurrence_id'] );
  }

  private function addRussianVendors()
  {
    foreach( array( 'tyumen', 'saint petersburg', 'omsk', 'almaty', 'novosibirsk', 'krasnoyarsk', 'moscow' ) as $city )
    {
        $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
          'city' => $city,
          'language' => 'ru',
          'time_zone' => 'Europe/Moscow',
          )
        );
        $vendor->save();
    }
    $this->assertEquals( 7, Doctrine::getTable( 'Vendor' )->count() );
  }

  private function getVenueIdsFromXml()
  {
    $venues = $this->eventsXml->xpath('//venue');
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
    $russianVendors = Doctrine::getTable( 'Vendor' )->findByLanguage( 'ru' );
    $this->assertEquals( 7, $russianVendors->count(), 'Should have 7 Russian Vendors' );

    for( $i = 0; $i < count( $venueIds ); $i++ )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'vendor_poi_id' ] = $venueIds[ $i ];
      $poi[ 'Vendor' ] = $russianVendors[ $i ];
      $poi->save();
    }
    $this->assertEquals( 7, Doctrine::getTable( 'Poi' )->count() );

    foreach( $russianVendors as $vendor )
    {
      $this->assertEquals( 1, $vendor[ 'Poi' ]->count() );
    }
  }

}
?>
