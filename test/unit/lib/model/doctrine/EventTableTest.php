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
    $occurrence[ 'start' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 2;
    $occurrence[ 'start' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 3;
    $occurrence[ 'start' ] = date( 'Y-m-d' );
    $occurrence[ 'utc_offset' ] = '0';
    $occurrence[ 'event_id' ] = $event[ 'id' ];
    $occurrence[ 'poi_id' ] = $poi2[ 'id' ];
    $occurrence->save();

    $occurrence = new EventOccurrence();
    $occurrence[ 'vendor_event_occurrence_id' ] = 4;
    $occurrence[ 'start' ] = date( 'Y-m-d' );
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
}
?>
