<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Event Model
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
class EventTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Event
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $poi1 = ProjectN_Test_Unit_Factory::add( 'poi' );

    $vendor2 = ProjectN_Test_Unit_Factory::add( 'vendor' );

    $poi2 = ProjectN_Test_Unit_Factory::get( 'poi' );
    $poi2->link( 'Vendor', array( $vendor2->getId() ) );
    $poi2->save();

    $eventCategory = new EventCategory();
    $eventCategory->setName( 'event category 1' );
    $eventCategory->save();

    $event = new Event();
    $event['vendor_event_id'] = 1111;
    $event->setName( 'test event1' );
    $event->link( 'Vendor', array( 1 ) );
    $event->save();

    $eventOccurrence1 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $eventOccurrence1['vendor_event_occurrence_id'] = 1;
    $eventOccurrence1['utc_offset'] = '-05:00:00';
    $eventOccurrence1->link( 'Event', array( $event['id'] ) );
    $eventOccurrence1->link( 'Poi', array( 1 ) );
    $eventOccurrence1->save();

    $eventOccurrence2 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $eventOccurrence2['vendor_event_occurrence_id'] = 2;
    $eventOccurrence2['utc_offset'] = '-05:00';
    $eventOccurrence2->link( 'Event', array( $event['id'] ) );
    $eventOccurrence2->link( 'Poi', array( 1 ) );
    $eventOccurrence2->save();

    $eventOccurrence3 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $eventOccurrence3['vendor_event_occurrence_id'] = 3;
    $eventOccurrence3['utc_offset'] = '-05:00:00';
    $eventOccurrence3->link( 'Event', array( $event['id'] ) );
    $eventOccurrence3->link( 'Poi', array( 2 ) );
    $eventOccurrence3->save();

    $this->object = Doctrine::getTable('Event')->findOneById( $event['id'] );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * test if the add property adds the properties
   */
  public function testAddProperty()
  {
    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('Event')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test prop lookup', $this->object[ 'EventProperty' ][ 0 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value', $this->object[ 'EventProperty' ][ 0 ][ 'value' ] );

    $this->assertEquals( 'test prop lookup 2', $this->object[ 'EventProperty' ][ 1 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value 2', $this->object[ 'EventProperty' ][ 1 ][ 'value' ] );

    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('Event')->findOneById( $this->object['id'] );

    $this->assertEquals( 2, $this->object['EventProperty']->count() );
  }

  /*
   * test if the add vendor category are added correctly
   */
  public function testAddVendorCategory()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->addVendorCategory( array( 'test parent cat', 'test cat' ), $vendor[ 'id' ] );
    $this->object->save();

    $this->object = Doctrine::getTable('Event')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test cat', $this->object[ 'VendorEventCategories' ][ 0 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategories' ][ 0 ][ 'vendor_id' ] );
    $this->assertEquals( 'test parent cat | test cat', $this->object[ 'VendorEventCategories' ][ 1 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategories' ][ 1 ][ 'vendor_id' ] );
  }

  /*
   * test if the add vendor category are added correctly
   */
  public function testAddVendorCategoryDoesntAddDuplicateCategories()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->save();
    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->save();

    $categoryTable = Doctrine::getTable( 'VendorEventCategory' );
    $this->assertEquals( 1, $categoryTable->count() );

    $this->object->addVendorCategory( 'test cat 2', $vendor[ 'id' ] );
    
    //@todo fix duplicate vendor categories
    $this->markTestIncomplete();

    $this->object->addVendorCategory( 'test cat 2', $vendor[ 'id' ] );
    $this->object->save();

    $categoryTable = Doctrine::getTable( 'VendorEventCategory' );
    $this->assertEquals( 2, $categoryTable->count() );
  }

  /**
   *
   */
  public function testGetPois()
  {
    $pois = $this->object['pois'];

    $this->assertEquals( 2, count( $pois ) );
    $this->assertTrue( $pois instanceof Doctrine_Collection );
    $this->assertEquals( 1, $pois[0]['id'] );
    $this->assertEquals( 2, $pois[1]['id'] );
  }
}
?>
