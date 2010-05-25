<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Event Table Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class EventTableTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var EventTable
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $poi = ProjectN_Test_Unit_Factory::add( 'poi' );
    $poi2 = ProjectN_Test_Unit_Factory::add( 'poi' );

    $eventCategory = new EventCategory();
    $eventCategory->setName( 'event category 1' );
    $eventCategory->save();

    $event = new Event();
    $event['vendor_event_id'] = 1111;
    $event->setName( 'test event1' );
    $event->link( 'Vendor', array( 1 ) );
    $event->save();

    $event2 = new Event();
    $event2['vendor_event_id'] = 1111;
    $event2->setName( 'test event1' );
    $event2->link( 'Vendor', array( 1 ) );
    $event2->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 1;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 2;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 3;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 4;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event2[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $occurrence->save();

    $this->object = Doctrine::getTable('Event');
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
   * testFindWithOccurrencesOrderedByPois
   */
  public function testFindWithOccurrencesOrderedByPois()
  {
    $events = $this->object->findWithOccurrencesOrderedByPois();

    $this->assertEquals( 3, count( $events[ 0 ][ 'EventOccurrence' ] ) );
  }

  /**
   * test getVendorUidFieldName() returns the right string
   */
  public function testGetVendorUidFieldName()
  {
    $column = $this->object->getVendorUidFieldName();
    $this->assertTrue( $this->object->hasColumn( $column ) );
  }

  /**
   * 
   */
   public function testFindByVendorAndStartsFrom()
   {
     ProjectN_Test_Unit_Factory::destroyDatabases();
     ProjectN_Test_Unit_Factory::createDatabases();
     $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
     $poi    = ProjectN_Test_Unit_Factory::add( 'Poi' );

     //add a good event (has occurrences that start today or later)
     $event  = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event[ 'Vendor' ] = $vendor;
     $event->save();

     $numOccurrencesStartingToday = 2;
     for( $i = 0; $i < $numOccurrencesStartingToday; $i++) 
     {
       $this->addEventOccurrence( $vendor, $poi, $event, ProjectN_Test_Unit_Factory::today() );
     }

     $numOccurencesStartingBeforeToday = 2;
     for( $i = 0; $i < $numOccurencesStartingBeforeToday; $i++) 
     {
       $this->addEventOccurrence( $vendor, $poi, $event, '2000-01-01' );
     }

     //add a bad event (has only occurrences that start before today)
     $event2 = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event2[ 'Vendor' ] = $vendor;
     $event2->save();

     for( $i = 0; $i < 4; $i++) 
     {
       $this->addEventOccurrence( $vendor, $poi, $event2, '2000-01-01' );
     }


     $numOccurrencesInTotal = $numOccurrencesStartingToday + $numOccurencesStartingBeforeToday;

     $event = Doctrine::getTable( 'Event' )->findOneById( 1 );
     $this->assertEquals( $numOccurrencesInTotal, $event[ 'EventOccurrence' ]->count() );

     $goodEvents = Doctrine::getTable( 'Event' )
      ->findByVendorAndStartsFromAsArray( $vendor, new DateTime );
     $goodEvent = $goodEvents[0];
     $this->assertEquals( 2, count( $goodEvent[ 'EventOccurrence' ] ) );

     $this->assertEquals( 1, count( $goodEvents ) );
   }

   public function testFindByVendorAndStartsFromOrder()
   {
     ProjectN_Test_Unit_Factory::destroyDatabases();
     ProjectN_Test_Unit_Factory::createDatabases();

     $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
     $poi1    = ProjectN_Test_Unit_Factory::add( 'Poi' );
     $poi2    = ProjectN_Test_Unit_Factory::add( 'Poi' );
     $poi3    = ProjectN_Test_Unit_Factory::add( 'Poi' );

     $event  = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event[ 'Vendor' ] = $vendor;
     $event->save();
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi2, $event, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi1, $event, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi2, $event, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today() );

     $event2  = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event2[ 'Vendor' ] = $vendor;
     $event2->save();
     $this->addEventOccurrence( $vendor, $poi3, $event2, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi1, $event2, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi2, $event2, ProjectN_Test_Unit_Factory::today() );

     $events = Doctrine::getTable( 'Event' )->findByVendorAndStartsFrom( $vendor, new DateTime );

     $occurrences = $events[0]['EventOccurrence'];
     $this->assertEquals( 1, $occurrences[ 0 ][ 'poi_id' ] );
     $this->assertEquals( 2, $occurrences[ 1 ][ 'poi_id' ] );
     $this->assertEquals( 2, $occurrences[ 2 ][ 'poi_id' ] );
     $this->assertEquals( 3, $occurrences[ 3 ][ 'poi_id' ] );
     $this->assertEquals( 3, $occurrences[ 4 ][ 'poi_id' ] );
     $this->assertEquals( 3, $occurrences[ 5 ][ 'poi_id' ] );

     $occurrences = $events[1]['EventOccurrence'];
     $this->assertEquals( 1, $occurrences[ 0 ][ 'poi_id' ] );
     $this->assertEquals( 2, $occurrences[ 1 ][ 'poi_id' ] );
     $this->assertEquals( 3, $occurrences[ 2 ][ 'poi_id' ] );
   }

   public function testFindByVendorEventIdAndVendorLanguage()
   {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendorData = array(
      array( 'city' => 'lisbon', 'language' => 'pt' ),
      array( 'city' => 'moscow', 'language' => 'ru' ),
      array( 'city' => 'london', 'language' => 'en' ),
    );

    $vendors = array();
    foreach( $vendorData as $data )
      $vendors[] = ProjectN_Test_Unit_Factory::add( 'Vendor', $data );

    $this->assertEquals( 3, Doctrine::getTable( 'Vendor' )->count() );

    foreach( $vendors as $vendor )
    {
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $vendor;
      $event['vendor_event_id'] = '1234';
      $event->save();
      $this->assertEquals( 1, $vendor['Event']->count() );
    }

    $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage( '1234', 'ru' );
    $this->assertEquals( 'moscow', $event['Vendor']['city'] );
   }

   private function addEventOccurrence( $vendor, $poi, $event, $date )
   {
       $occurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
       $occurrence[ 'start_date' ] = $date;
       $occurrence[ 'Event' ] = $event;
       $occurrence[ 'Poi' ]   = $poi;
       $occurrence->save();
   }
}
?>
