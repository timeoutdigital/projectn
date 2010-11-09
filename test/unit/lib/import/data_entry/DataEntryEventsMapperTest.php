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

    // Load Fixtures to create Vendors
    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'london' );

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;
    // For @#'#@# reason, this is not overriding the Vendor ID ??? it's always 1?
    $londonPoi  = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => 7912, 'vendor_id' => 4  ) ); //fixture event happens at this venue
    $londonPoi2 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => 7913 , 'vendor_id' => 4  ) ); //fixture event happens at this venue

    $londonPoi2['Vendor'] = $londonPoi['Vendor'] = $this->vendor; // overriding the Vendor
    $londonPoi->save();
    $londonPoi2->save();

    $this->object = new DataEntryImportManager( 'london',  $importDir );

    $this->object->importEvents( );
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
    $this->assertNull( $event['short_description'] ); // refs #538
    $this->assertEquals( 'Guided tours of 20 areas of London, including Spitalfields, Highgate and Mayfair, recorded on CD or available to download on to MP3 players. Each walk lasts two hours and comes with a map.', $event['description'] );
    $this->assertEquals( 'http://www.timeout.com', $event['booking_url'] );
    $this->assertEquals( 'http://www.timeout.com/london', $event['url'] );
    $this->assertEquals( '10', $event['price'] );
    $this->assertEquals( '1', $event['rating'] );
    $this->assertEquals( '4', $event['vendor_id'] );

    $vendorCategories = $event['VendorEventCategory']->toArray();

    $this->assertEquals( 'London', $vendorCategories[ 'London'] ['name'] );
    $this->assertEquals( 'Around Town', $vendorCategories[ 'Around Town'] ['name'] );
    $eventOccurrence1 = $event['EventOccurrence'][0];

    $this->assertEquals( '7883_7912_20100712_100000', $eventOccurrence1['vendor_event_occurrence_id'] );
    $this->assertEquals( '2010-07-12', $eventOccurrence1['start_date'] );
    $this->assertEquals( '10:00:00', $eventOccurrence1['start_time'] );
    $this->assertEquals( '11:00:00', $eventOccurrence1['end_time'] );
    $this->assertEquals( '+00:00', $eventOccurrence1['utc_offset'] );

    $eventOccurrence2 = $event['EventOccurrence'][1];
    $this->assertEquals( '2010-07-13', $eventOccurrence2['start_date'] );
    $this->assertEquals( '10:00:01', $eventOccurrence2['start_time'] );
    $this->assertEquals( '11:00:01', $eventOccurrence2['end_time'] );
    $this->assertEquals( '+00:00', $eventOccurrence2['utc_offset'] );

    $this->assertGreaterThan( 0, $event[ 'EventProperty' ]->count() );
    $this->assertEquals( 'http://www.timeout.ru/cinema/event/15032/', $event[ 'EventProperty' ][0] ['value']  );
    $this->assertEquals( 'Timeout_link', $event[ 'EventProperty' ][0] ['lookup']  );

    $this->assertGreaterThan( 0, $event[ 'EventMedia' ]->count() );
    $this->assertEquals( 'http://projectn.s3.amazonaws.com/london/event/media/ffadd72901dbc30f69330db7beb5fef6e2ccdcdc.jpg', $event[ 'EventMedia' ][0] ['url']  );
    // Since the Image Task separated, our Db will store image URl as it is and later image download task will download & export to amazon


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
    $this->object->importEvents( );

    $events = Doctrine::getTable('Event')->findAll();
    $event = $events[ 0 ];
    $occurrence2 = $event['EventOccurrence'][0];

    $this->assertEquals( $occurrence['vendor_event_occurrence_id'], $occurrence2['vendor_event_occurrence_id']);
    $this->assertEquals( $occurrence['id'], $occurrence2['id']);
    $this->assertEquals( $occurrence['event_id'], $occurrence2['event_id']);

  }



  public function testImportUpdatingExistingRecords()
  {
     $events = Doctrine::getTable('Event')->findAll();

     $this->assertEquals( 2,  count( $events ));
     $event1 = $events[ 0 ];
     $updatedAt1 = $event1[ 'updated_at' ];

     //running the import with the same data so the import should update the existing ones
     sleep( 1 ); //sleep for  a second, need to see the updated_at is changing

     $this->object->importEvents( );
     $events = Doctrine::getTable('Event')->findAll();
     $this->assertEquals( 2,  count( $events ) , 'after running the import twice in a row, the event count shouldn"t change');
     $event2 = $events[ 0 ];
     $updatedAt2 = $event2[ 'updated_at' ];

     //check the event1 and event2 are same event
     $this->assertNotEquals( $updatedAt1 , $updatedAt2   );
     $this->assertEquals( $event1[ 'id' ],  $event2[ 'id' ] );
     $this->assertEquals( $event1[ 'name' ],  $event2[ 'name' ] );
     $this->assertEquals( $event1[ 'vendor_id' ],  $event2[ 'vendor_id' ] );
     $this->assertEquals( count( $event1['EventOccurrence'] ),  count( $event2['EventOccurrence'] ) );


  }

  public function  testDataEntryUpdate()
  {

    /** if this test doesn't make any sense please look into the test with the same name in DataEntryPoiMapperTest. there is some explanation there
     * and it's basicly the same thing here
     * in the update XML the name and the url is changed
     **/
     $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR .
                  'updateXMLs' .DIRECTORY_SEPARATOR
                  ;

    //setting to use the vendor_event_id to actually update the event NOT insert a new one!
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,true );

    $this->object = new DataEntryImportManager( 'london', $importDir);

    $this->object->importEvents( );

    $event = Doctrine::getTable( 'Event' )->find( 1 );

    $this->assertEquals( 'Footnotes Audio Walks - this event is cancelled' , $event[ 'name' ] );

    $this->assertEquals( 'http://www.google.com' , $event[ 'url' ] );
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,false );
  }
}
