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
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class RussiaFeedEventsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var RussiaFeedEventsMapper
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

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
    $this->vendor = $vendor;

    $this->object = new RussiaFeedEventsMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/russia_events.short.xml' ),
      null,
      "moscow"
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

  public function testMapPlaces()
  {
    //get all venue ids from events xml
    $eventsOccurrenceVenues = simplexml_load_file( TO_TEST_DATA_PATH . '/russia_events.short.xml' )->xpath('//venue');
    $venueIds = array();
    foreach( $eventsOccurrenceVenues as $venue ) $venueIds[] = (string) $venue;
    $venueIds = array_unique( $venueIds );

    $russianVendors = Doctrine::getTable( 'Vendor' )->findByLanguage( 'ru' );
    $index = 0;
    
    $vendor = $russianVendors[5];

    foreach( $venueIds as $venueId )
    {
        $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $poi['vendor_poi_id'] = $venueId;
        $poi['Vendor'] = $vendor;
        $poi->save();
    }

    var_dump( Doctrine::getTable('Vendor')->count() . ' pois.' );
    
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();
    
    $events = Doctrine::getTable('Event')->findAll();
    $this->assertEquals( 30, $events->count(), 'Should have same number of events imported as in the feed received.' );
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
}
?>
