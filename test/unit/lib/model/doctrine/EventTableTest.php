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
    $event['vendor_id'] = 1;
    $event->save();

    $event2 = new Event();
    $event2['vendor_event_id'] = 1111;
    $event2->setName( 'test event1' );
    $event2['vendor_id'] = 1;
    $event2->save();

    /**
     * When adding Occurrences, we have to take into account that Only unique occurrences wil be saved to Database..
     * unique == event_id + statr_date + start_time + poi_id
     */
    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 1;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $event['EventOccurrence'][] = $occurrence;

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 2;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'start_time' ] = '10:20';
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'poi_id' ] = $poi[ 'id' ];
    $event['EventOccurrence'][] = $occurrence;

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 3;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'start_time' ] = '12:25';
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $event['EventOccurrence'][] = $occurrence;

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 4;
    $occurrence[ 'start_date' ] = date( 'Y-m-d' );
    $occurrence[ 'start_time' ] = '15:00';
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $event2['EventOccurrence'][] = $occurrence;

    // Save Events again to save Occurrences and update LINKS
    $event->save();
    $event2->save();

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
       $this->addEventOccurrence( $vendor, $poi, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + $i) ) );
     }

     $numOccurencesStartingBeforeToday = 2;
     for( $i = 0; $i < $numOccurencesStartingBeforeToday; $i++) 
     {
       $this->addEventOccurrence( $vendor, $poi, $event, '2000-01-01', date( 'H:i:s', (time() + $i) ) );
     }

     //add a bad event (has only occurrences that start before today)
     $event2 = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event2[ 'Vendor' ] = $vendor;
     $event2->save();

     for( $i = 0; $i < 4; $i++) 
     {
       $this->addEventOccurrence( $vendor, $poi, $event2, '2000-01-01', date( 'H:i:s', (time() + $i) ) );
     }


     $numOccurrencesInTotal = $numOccurrencesStartingToday + $numOccurencesStartingBeforeToday;

     $event = Doctrine::getTable( 'Event' )->findOneById( 1 );
     $this->assertEquals( $event[ 'EventOccurrence' ]->count(), $numOccurrencesInTotal);

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
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 1) ) );
     $this->addEventOccurrence( $vendor, $poi2, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 2) ) );
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 3) ) );
     $this->addEventOccurrence( $vendor, $poi1, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 4) ) );
     $this->addEventOccurrence( $vendor, $poi2, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 5) ) );
     $this->addEventOccurrence( $vendor, $poi3, $event, ProjectN_Test_Unit_Factory::today(), date( 'H:i:s', (time() + 6) ) );

     $event2  = ProjectN_Test_Unit_Factory::get( 'Event' );
     $event2[ 'Vendor' ] = $vendor;
     $event2->save();
     $this->addEventOccurrence( $vendor, $poi3, $event2, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi1, $event2, ProjectN_Test_Unit_Factory::today() );
     $this->addEventOccurrence( $vendor, $poi2, $event2, ProjectN_Test_Unit_Factory::today() );

     $events = Doctrine::getTable( 'Event' )->findByVendorAndStartsFromAsArray( $vendor, new DateTime );

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

   private function addEventOccurrence( $vendor, $poi, $event, $date, $start_time = null )
   {
       $occurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
       $occurrence[ 'start_date' ] = $date;
       $occurrence[ 'start_time' ] = $start_time;
       $occurrence[ 'Poi' ]   = $poi;
       $event['EventOccurrence'][] = $occurrence;
       $event->save();
   }

   public function testFindForExportExlucdeExpiredEvents()
   {
       // Some Models we need to test Event
       $vendor = Doctrine::getTable( 'Vendor' )->find( 1 );
       $poi = Doctrine::getTable( 'Poi' )->find(1);
       
       // This event has 3 Valid Not expired Occurrences by Default (setup() )
       $event = Doctrine::getTable( 'Event' )->find( 1 );
       $this->assertEquals( 3, $event['EventOccurrence']->count() );

       // test Export Call findForExport();
       $eventsForExport = Doctrine::getTable( 'Event' )->findForExport( $vendor );
       $this->assertEquals( 2, count( $eventsForExport ), 'There were TWO events inserted during setup' );
       $this->assertEquals( 3, count( $eventsForExport[0]['EventOccurrence'] ), 'Three Occurrences are Valid and not expired');

       // Insert 1 Expied occurrence and 1 more future dated occurrence
       $this->addEventOccurrence( $vendor, $poi, $event, date('Y-m-d', strtotime( '+1 Day' ) ) ); // Future dated Occurrence
       $this->addEventOccurrence( $vendor, $poi, $event, date('Y-m-d', strtotime( '-2 Day' ) ) ); // Expired Occurrence
       $this->assertEquals( 5, $event['EventOccurrence']->count(), 'This event should have 5 Occurrences' );

       // call findForExport() again to Excude this Expired and it should only return 4 occurrences
       $eventsForExport = Doctrine::getTable( 'Event' )->findForExport( $vendor );
       $this->assertEquals( 4, count( $eventsForExport[0]['EventOccurrence'] ), 'It should have 4 future dated occurrences');
   }
   
   public function testFindForExportIncludeExpiredEvents()
   {
       // Some Models we need to test Event
       $vendor = Doctrine::getTable( 'Vendor' )->find( 1 );
       $poi = Doctrine::getTable( 'Poi' )->find(1);

       // This event has 3 Valid Not expired Occurrences by Default (setup() )
       $event = Doctrine::getTable( 'Event' )->find( 1 );
       $this->assertEquals( 3, $event['EventOccurrence']->count() );

       // Insert 1 Expied occurrence and 1 more future dated occurrence
       $this->addEventOccurrence( $vendor, $poi, $event, date('Y-m-d', strtotime( '+1 Day' ) ) ); // Future dated Occurrence
       $this->addEventOccurrence( $vendor, $poi, $event, date('Y-m-d', strtotime( '-2 Day' ) ) ); // Expired Occurrence
       $this->assertEquals( 5, $event['EventOccurrence']->count(), 'This event should have 5 Occurrences' );

       // call findForExport() again, this time it should export expired occurrences as well
       $eventsForExport = Doctrine::getTable( 'Event' )->findForExport( $vendor, true ); // note the switch is TRUE now
       $this->assertEquals( 5,  count( $eventsForExport[0]['EventOccurrence'] ), 'It should have 5  = ( future dated + exired ) occurrences');
   }


}
?>
