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

    $this->assertEquals( 'test cat', $this->object[ 'VendorEventCategory' ][ 'test cat' ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategory' ][ 'test cat' ][ 'vendor_id' ] );
    $this->assertEquals( 'test parent cat | test cat', $this->object[ 'VendorEventCategory' ][ 'test parent cat | test cat' ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategory' ][ 'test parent cat | test cat' ][ 'vendor_id' ] );
  }

  /*
   * test if the add vendor category are added correctly
   */
  public function testAddVendorCategoryDoesntAddDuplicateCategories()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
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

  public function testEventCategories()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'Vendor' ] = $vendor;

    //add vendor category called 'foo'
    $event->addVendorCategory( 'foo', $vendor['id'] );
    $event->save();
    $foo = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'foo', $vendor['id'] );
    $this->assertEquals( 'foo', $foo['name'] );

    //map 'foo' to new EventCategory 'bar'
    $this->mapVendorCategoryToEventCategory( $foo, 'bar' );
    $this->assertEquals( 1, count( $event[ 'EventCategory' ] ), 'Event should have one EventCategory' );
    $this->assertEquals( 'bar', $event[ 'EventCategory' ][ 0 ][ 'name' ] );

    //add vendor category called 'one'
    $event->addVendorCategory( 'one', $vendor['id'] );
    $event->save();
    $one = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'one', $vendor['id'] );
    $this->assertEquals( 'one', $one['name'] );
    $this->assertEquals( 2, Doctrine::getTable( 'VendorEventCategory' )->count(), 'Should have two VendorEventCategory records in table.' );

    //map 'one' to new EventCategory 'uno'
    $this->mapVendorCategoryToEventCategory( $one, 'uno' );
    $this->assertEquals( 2, count( $event[ 'EventCategory' ] ) );
    $this->assertEquals( 'uno', $event[ 'EventCategory' ][ 1 ][ 'name' ] );

    //map 'one' to new EventCategory 'ein'
    $this->mapVendorCategoryToEventCategory( $one, 'ein' );
    $this->assertEquals( 3, Doctrine::getTable( 'EventCategory' )->count() );

    $ein = Doctrine::getTable( 'EventCategory' )->findOneByName( 'ein' );
    $this->assertEquals( 1, count( $ein[ 'VendorEventCategory' ] ) );
    $this->assertEquals( 'one', $ein[ 'VendorEventCategory' ][ 0 ][ 'name' ] );

    $this->assertEquals( 3, count( $event[ 'EventCategory' ] ) );
    $this->assertEquals( 'ein', $event[ 'EventCategory' ][ 2 ][ 'name' ] );

    //do another event with another vendor
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'Vendor' ] = $vendor;

    //add vendor category called 'foo'
    $event->addVendorCategory( 'foo', $vendor['id'] );
    $event->save();
    $foo = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'foo', $vendor['id'] );
    $this->assertEquals( 'foo', $foo['name'] );

    //map 'foo' to new EventCategory 'bar'
    $this->mapVendorCategoryToEventCategory( $foo, 'bar' );
    $this->assertEquals( 1, count( $event[ 'EventCategory' ] ), 'Event should have one EventCategory' );
    $this->assertEquals( 'bar', $event[ 'EventCategory' ][ 0 ][ 'name' ] );
  }

  private function mapVendorCategoryToEventCategory( $vendorEventCategory, $eventCategoryName )
  {
    $vendorEventCategoryTable = Doctrine::getTable( 'VendorEventCategory' );

    $eventCategory = new EventCategory();
    $eventCategory[ 'name' ] = $eventCategoryName;
    $eventCategory[ 'VendorEventCategory' ][] = $vendorEventCategory;
    $eventCategory->save();
  }

  /**
   *
   * test the  getter and setter functions for the Critics_choice flag
   */
  public function testSetterGetterCriticsChoiceFlag()
  {
    $this->object['CriticsChoiceProperty'] = true;
    $this->assertEquals( 'Y', $this->object['CriticsChoiceProperty'] );

    //see todo in subject class
    //$this->object['CriticsChoiceProperty'] = false;
    //$this->assertNull( $this->object['CriticsChoiceProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setCriticsChoiceProperty( 'not a boolean' );
    $this->assertNull( $this->object->getCriticsChoiceProperty() );
  }

  /**
   *
   * test the  getter and setter functions for the Recommended flag
   */
  public function testSetterGetterRecommendedFlag()
  {
    $this->object['RecommendedProperty'] = true;
    $this->assertEquals( 'Y', $this->object['RecommendedProperty'] );

    //$this->object['RecommendedProperty'] = false;
    //$this->assertNull( $this->object['RecommendedProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setRecommendedProperty('not a boolean');
    $this->assertNull( $this->object->getRecommendedProperty() );
  }

  /**
   *
   * test the  getter and setter functions for the Free flag
   */
  public function testSetterGetterFreeFlag()
  {
    $this->object['FreeProperty'] = true;
    $this->assertEquals( 'Y', $this->object['FreeProperty'] );

    //$this->object['RecommendedProperty'] = false;
    //$this->assertNull( $this->object['RecommendedProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setFreeProperty('not a boolean');
    $this->assertNull( $this->object->getFreeProperty() );
  }

  public function testAddTimeoutUrl()
  {
    $this->object['TimeoutLinkProperty'] = '';
    $this->assertNull( $this->object['TimeoutLinkProperty'] );

    $url = 'http://www.timeout.com/london/event/123';
    $this->object['TimeoutLinkProperty'] = $url;
    $this->assertEquals( $url, $this->object['TimeoutLinkProperty'] );
  }

   public function testAddMediaByUrlandSavePickLargerImage()
   {
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );

    $event[ 'Vendor' ] = $vendor;

    //$poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $mediumImageUrl   = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h217/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $event->addMediaByUrl( $smallImageUrl );
    $event->addMediaByUrl( $largeImageUrl );
    $event->addMediaByUrl( $mediumImageUrl );

    $event->save();

    $savedEventId = $event->id;
    $event->free( true ); unset( $event );
    $event = Doctrine::getTable( "Event" )->findOneById( $savedEventId );

    // after adding 3 images we expect to have only one image and it should be the large image
    $this->assertEquals( count( $event[ 'EventMedia' ]) ,1 , 'there should be only one EventMedia attached to a Event after saving' );
    $this->assertEquals( $event[ 'EventMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be attached to Event when adding more than one' );

   }

   /**
    * if there is an image attached to Event and a smaller one is being added, it should keep the larger image
    *
    */
   public function testAddMediaByUrlandSaveSkipSmallerImage()
   {
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );

    $event[ 'Vendor' ] = $vendor;

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $event->addMediaByUrl( $largeImageUrl );
    $event->save();

    $savedEventId = $event->id;
    $event->free( true ); unset( $event );
    $event = Doctrine::getTable( "Event" )->findOneById( $savedEventId );

    // adding a smaller size imahe
    $event->addMediaByUrl( $smallImageUrl );
    $event->save();

    $this->assertEquals( count( $event[ 'EventMedia' ]) ,1 , 'there should be only one EventMedia attached to a Event after saving' );
    $this->assertEquals( $event[ 'EventMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be kept adding a smaller sized one' );

   }

    /**
    * if there is an image attached to event and a larger one is being added, it should remove the existing image with the larger one
    *
    */
   public function testAddMediaByUrlandSaveRemoveSmallerImageAndSaveLargerOne()
   {
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );

    $event[ 'Vendor' ] = $vendor;

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $event->addMediaByUrl( $smallImageUrl );
    $event->save();

    $savedEventId = $event->id;
    $event->free( true ); unset( $event );
    $event = Doctrine::getTable( "Event" )->findOneById( $savedEventId );

    // adding a smaller size imahe
    $event->addMediaByUrl( $largeImageUrl );
    $event->save();

    $this->assertEquals( count( $event[ 'EventMedia' ]) ,1 , 'there should be only one EventMedia attached to a Event after saving' );
    $this->assertEquals( $event[ 'EventMedia' ][0][ 'url' ], $largeImageUrl , 'larger should be saved' );

   }

}

?>
