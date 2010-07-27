<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of data entry Feed Events Mapper import.
 *
 * @package test
 *
 * @author emre basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DataEntryEventsMapperTest extends PHPUnit_Framework_TestCase
{

  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'london',
      'language' => 'en-GB',
      'time_zone' => 'Europe/London',
      'inernational_dial_code' => '+44',
      )
    );
    $vendor->save();

    $this->vendor = $vendor;

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;
    $londonPoi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => 7912 , 'vendor_id' => 1 ) ); //fixture event happens at this venue

    $londonPoi2 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => 7913 , 'vendor_id' => 1 ) ); //fixture event happens at this venue

    DataEntryImportManager::setImportDir( $importDir );

    DataEntryImportManager::importEvents( );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }


  public function testMapping()
  {
    $events = Doctrine::getTable('Event')->findAll();
    $this->assertGreaterThan( 1, $events->count() );
    $this->assertLessThan( 7, $events->count() );

    $event = $events[ 0 ];

    $this->assertEquals( '7883', $event['vendor_event_id'] );
    $this->assertEquals( 'Footnotes Audio Walks', $event['name'] );
    $this->assertEquals( 'some short description text goes here', $event['short_description'] );
    $this->assertEquals( 'Guided tours of 20 areas of London, including Spitalfields, Highgate and Mayfair, recorded on CD or available to download on to MP3 players. Each walk lasts two hours and comes with a map.', $event['description'] );
    $this->assertEquals( 'http://www.timeout.com', $event['booking_url'] );
    $this->assertEquals( 'http://www.timeout.com/london', $event['url'] );
    $this->assertEquals( '10', $event['price'] );
    $this->assertEquals( '1', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    $vendorCategories = $event['VendorEventCategory']->toArray();

    $this->assertEquals( 'London', $vendorCategories[ 'London'] ['name'] );
    $this->assertEquals( 'Around Town', $vendorCategories[ 'Around Town'] ['name'] );
    $eventOccurrence1 = $event['EventOccurrence'][0];

    $this->assertEquals( '2010-07-12', $eventOccurrence1['start_date'] );
    $this->assertEquals( '10:00:00', $eventOccurrence1['start_time'] );
    $this->assertEquals( '11:00:00', $eventOccurrence1['end_time'] );
    $this->assertEquals( '+01:00', $eventOccurrence1['utc_offset'] );

    $eventOccurrence2 = $event['EventOccurrence'][1];
    $this->assertEquals( '2010-07-13', $eventOccurrence2['start_date'] );
    $this->assertEquals( '10:00:01', $eventOccurrence2['start_time'] );
    $this->assertEquals( '11:00:01', $eventOccurrence2['end_time'] );
    $this->assertEquals( '+01:00', $eventOccurrence2['utc_offset'] );

    $this->assertGreaterThan( 0, $event[ 'EventProperty' ]->count() );
    $this->assertEquals( 'http://www.timeout.ru/cinema/event/15032/', $event[ 'EventProperty' ][0] ['value']  );
    $this->assertEquals( 'Timeout_link', $event[ 'EventProperty' ][0] ['lookup']  );

    $this->assertGreaterThan( 0, $event[ 'EventMedia' ]->count() );
    $this->assertEquals( 'http://projectn.s3.amazonaws.com/sydney/event/media/83aad34e323dd5d56c43701d2387ac90.jpg', $event[ 'EventMedia' ][0] ['url']  );


    $eventOccurrence3 = $event['EventOccurrence'][3];
    $this->assertEquals( '2010-08-12', $eventOccurrence3['start_date'] );
    $this->assertEquals( '10:00:01', $eventOccurrence3['start_time'] );
    $this->assertEquals( '11:00:01', $eventOccurrence3['end_time'] );

  }

  public function testOccurrenceIdsAreSameAfterTwoImports()
  {
    $events = Doctrine::getTable('Event')->findAll();
    $this->assertGreaterThan( 1, $events->count() );
    $this->assertLessThan( 7, $events->count() );

    $event = $events[ 0 ];

    $occurrence = $event['EventOccurrence'][0];

    //run the import again
    DataEntryImportManager::importEvents( );

    $events = Doctrine::getTable('Event')->findAll();
    $event = $events[ 0 ];
    $occurrence2 = $event['EventOccurrence'][0];

    $this->assertEquals( $occurrence['vendor_event_occurrence_id'], $occurrence2['vendor_event_occurrence_id']);
    $this->assertEquals( $occurrence['id'], $occurrence2['id']);
    $this->assertEquals( $occurrence['event_id'], $occurrence2['event_id']);

  }
}