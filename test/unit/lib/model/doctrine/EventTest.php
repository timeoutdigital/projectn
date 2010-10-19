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
    Event::resetVendorCategoryCache();
  }

  private function initializeEvent()
  {
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

  /**
   * Ensure that a Doctrine record doesn't save/update unless
   * it's content changes
   */
  public function testUnmodifiedDoctrineRecordDoesNotSave()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $name = 'Super duper';
      $vendorCategory = 'Gowanus'; //found in NY

      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $event  = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event['Vendor'] = $vendor;
      $event['name'] = $name;
      $event->addVendorCategory( $vendorCategory );
      $event->save();
      $updatedAt = $event[ 'updated_at' ];

      $sameEvent = Doctrine::getTable( 'Event' )->findOneById( 1 );
      $event['Vendor'] = $vendor;
      $sameEvent[ 'name' ] = $name;
      $sameEvent->addVendorCategory( $vendorCategory );
      sleep( 2 );
      $sameEvent->save();
      $this->assertEquals( $updatedAt, $sameEvent[ 'updated_at' ] );
  }

   /**
   * test if setting the name of a Poi ensures HTML entities are decoded and Trimmed
   */
  public function testCleanStringFields()
  {
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $event['name'] = "Movie &quot;name&quot; is";

      // Add HTML Entities to all poi fields of type 'string'
      foreach( Doctrine::getTable( "Event" )->getColumns() as $column_name => $column_info )
      {
        if( $column_info['type'] == 'string' )
        {
            if( is_string( @$event[ $column_name ] ) )
            {
                $event[ $column_name ] .= "&sect;";
            }
        }
      }

      $event->save();

      $this->assertTrue( preg_match( '/&quot;/', $event['name'] ) == 0, 'POI name cannot contain HTML entities' );

      // Check HTML Entities for all poi fields of type 'string'
      foreach( Doctrine::getTable( "Event" )->getColumns() as $column_name => $column_info )
      {
        if( $column_info['type'] == 'string' )
        {
            if( is_string( @$event[ $column_name ] ) )
            {
                $this->assertTrue( preg_match( '/&sect;/', $event[ $column_name ] ) == 0, 'Failed to convert &sect; to correct symbol' );
            }
        }
      }

      // Refs #525 Trim test
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $event['name'] = "    This is event, Not a movie  ";
      $event['description'] = "<p>a description with tab <br />and space ends</p>      ";

      $event->save();

      // Assert
      $this->assertEquals('This is event, Not a movie', $event['name']);
      $this->assertEquals('<p>a description with tab <br />and space ends</p>', $event['description']);


      // make sure leading and trailing commas get removed
      $event['name'] = ',Event name is ,';

      // save
      $event->save();

      // assert
      $this->assertEquals('Event name is', $event['name'], 'trim failed to remove leading and/or trailing comma(s)');

  }

  /*
   * test if the add property adds the properties
   */
  public function testAddProperty()
  {
    $this->initializeEvent();
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
    $this->initializeEvent();

    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->addVendorCategory( array( 'test parent cat', 'test cat' ), $vendor[ 'id' ] );
    $this->object->save();

    $this->object = Doctrine::getTable('Event')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test cat', $this->object[ 'VendorEventCategory' ][ 'test cat' ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategory' ][ 'test cat' ][ 'vendor_id' ] );
    $this->assertEquals( 'test parent cat | test cat', $this->object[ 'VendorEventCategory' ][ 'test parent cat | test cat' ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorEventCategory' ][ 'test parent cat | test cat' ][ 'vendor_id' ] );

    $this->assertTrue( $this->object[ 'VendorEventCategory' ][ 'test cat' ]->exists() );
    $this->assertTrue( $this->object[ 'VendorEventCategory' ][ 'test parent cat | test cat' ]->exists() );
  }

  /*
   * test if the add vendor category are added correctly
   */
  public function testAddVendorCategoryDoesntAddDuplicateCategories()
  {
    //we need a vendor for our events and categories
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $this->assertEquals( 0, Doctrine::getTable( 'VendorEventCategory' )->count() );

    //Start with a category named 'Category One' to the database
    $event_old = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event_old[ 'Vendor' ] = $vendor;
    $event_old->addVendorCategory( 'Category One', $vendor );
    $event_old->save();

    // Check the Category is Added to DB
    $fetchedCategories = Doctrine::getTable( 'VendorEventCategory' )->findAll();
    $this->assertEquals( 1, $fetchedCategories->count() );
    $this->assertEquals( $vendor['id'], $fetchedCategories[0]['vendor_id']);

    //Adding an event with a category named 'Category One'...
    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'Vendor' ] = $vendor;
    $event->addVendorCategory( 'Category One', $vendor );
    $event->save();

    //must not create a new category...
    $fetchedCategories = Doctrine::getTable( 'VendorEventCategory' )->findAll();
    $this->assertEquals( 1, $fetchedCategories->count() );

    //must reuse existing instead
    $this->assertEquals( 'Category One', $event['VendorEventCategory'][ 'Category One' ]['name'] );
    $this->assertTrue( $event['VendorEventCategory'][ 'Category One' ]->exists() );


    //Adding category named 'Category Two' to the event...
    $event->addVendorCategory( 'Category Two', $vendor );
    $event->save();

    //should create a new category
    $this->assertEquals( 2, Doctrine::getTable( 'VendorEventCategory' )->count() );
    $this->assertEquals( 'Category Two', $event['VendorEventCategory'][ 'Category Two' ]['name'] );
    $this->assertTrue( $event['VendorEventCategory'][ 'Category Two' ]->exists() );

    //Adding category named 'Category Two' to another event...
    $event2 = ProjectN_Test_Unit_Factory::add( 'Event' );
    $event2[ 'Vendor' ] = $vendor;
    $event2->addVendorCategory( 'Category Two', $vendor );
    $event2->save();

    //should not add 'Category Two' again...
    $this->assertEquals( 2, Doctrine::getTable( 'VendorEventCategory' )->count() );

    //event should reuse 'Category Two'
    $this->assertEquals( 'Category Two', $event2['VendorEventCategory'][ 'Category Two' ]['name'] );
    $this->assertTrue( $event['VendorEventCategory'][ 'Category Two' ]->exists() );
  }

  /**
   * Since #716 addVendorEventCategory uses Static variable to cache the Vendor Categories to avoid Database queris,
   * This test should prove that Different vendors are handles differently
   */
  public function testCrossVendorEventCategory()
  {
      // add 2 vendors and test that there is No Category Exists
      $vendor1 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $this->assertEquals( 1 , $vendor1['id']);
      $this->assertEquals( 2 , $vendor2['id']);
      $this->assertEquals( 0, Doctrine::getTable( 'VendorEventCategory' )->count() );

      //Adding an event with a category named 'Category One'...
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $vendor1;
      $event->addVendorCategory( 'Category One', $vendor1 );
      $event->save();

      // There should be 1 Caregory Now
      $this->assertEquals( 1, Doctrine::getTable( 'VendorEventCategory' )->count() );

      //Event with different Vendor, But Same Category name
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $vendor2;
      $event->addVendorCategory( 'Category One', $vendor2 );
      $event->save();

      // There should be 2 Caregory Now
      $this->assertEquals( 2, Doctrine::getTable( 'VendorEventCategory' )->count() );
  }

  /**
   * Check to see if addVendorCategory add's Empty array value array('')
   */
  public function testAddVendorCategoryEmpty()
  {
      $this->initializeEvent();

      $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

      // Add String Category
      $this->object->addVendorCategory( 'empty 1', $vendor[ 'id' ] );

      // Empty string
      $this->object->addVendorCategory( '', $vendor[ 'id' ] );

      // array
      $this->object->addVendorCategory( array('empty2 ', 'empty 3'), $vendor[ 'id' ] );

      // array plus empty
      $this->object->addVendorCategory( array( 'empty 4', '', ' ', ' empty 5' ), $vendor[ 'id' ] );

      $this->object->save(); // Save to DB

      // validate
      $categoryTable = Doctrine::getTable( 'VendorEventCategory' )->findAll();

      $this->assertEquals('empty 1' , $categoryTable[0]['name']);
      $this->assertEquals('empty2  | empty 3' , $categoryTable[1]['name']);
      $this->assertEquals('empty 4 |  empty 5' , $categoryTable[2]['name']);

      // Object Exception!
      try{

          $this->object->addVendorCategory($categoryTable, $vendor[ 'id' ] );
          $this->assertEquals(false, true, 'Error: addVendorCategory should throw an exception when an object passed as parameter');

      }catch(Exception $exception)
      {
          $this->assertEquals(false, false); // Exception captured

      }

      // @todo: addVendorCategory do not removes whitespaces in parameter
      $this->markTestIncomplete();

  }


  /**
   *
   */
  public function testGetPois()
  {
    $this->initializeEvent();
    $pois = $this->object['pois'];

    $this->assertEquals( 2, count( $pois ) );
    $this->assertTrue( $pois instanceof Doctrine_Collection );
    $this->assertEquals( 1, $pois[0]['id'] );
    $this->assertEquals( 2, $pois[1]['id'] );
  }

  public function testEventCategories()
  {

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'Vendor' ] = $vendor;

    //add vendor category called 'vendorCategoryName'
    $event->addVendorCategory( 'vendorCategoryName', $vendor['id'] );
    $event->save();
    $vendorCategory = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'vendorCategoryName', $vendor['id'] );

    //map 'foo' to new EventCategory 'bar'
    $this->mapVendorCategoryToEventCategory( $vendorCategory, 'EventCategoryName' );
    $this->assertEquals( 1, count( $event[ 'EventCategory' ] ), 'Event should have one EventCategory' );
    $this->assertEquals( 'EventCategoryName', $event[ 'EventCategory' ][ 0 ][ 'name' ] );

    //add vendor category called 'one'
    $event->addVendorCategory( 'one', $vendor['id'] );
    $event->save();
    $one = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'one', $vendor['id'] );
    $this->assertEquals( 'one', $one['name'] );
    $this->assertEquals( 2, Doctrine::getTable( 'VendorEventCategory' )->count(), 'Should have two VendorEventCategory records in table.' );

    //map 'one' to new EventCategory 'uno'
    $this->assertEquals( 1, count( $event[ 'EventCategory' ] ) ,'before mapping the second category, we should have 1 eventCategory' );
    $this->mapVendorCategoryToEventCategory( $one, 'uno' );
    $this->assertEquals( 2, count( $event[ 'EventCategory' ] ) ,'after mapping the second category, we should have 2 eventCategory' );
    $this->assertEquals( 'uno', $event[ 'EventCategory' ][ 1 ][ 'name' ] );

    //map 'one' to new EventCategory 'ein'
    $this->mapVendorCategoryToEventCategory( $one, 'ein' );
    $this->assertEquals( 3, Doctrine::getTable( 'EventCategory' )->count(), 'after mapping the third category, we should have 3 eventCategory'  );

    $ein = Doctrine::getTable( 'EventCategory' )->findOneByName( 'ein' );
    $this->assertEquals( 1, count( $ein[ 'VendorEventCategory' ] ) );
    $this->assertEquals( 'one', $ein[ 'VendorEventCategory' ][ 0 ][ 'name' ] );

    $this->assertEquals( 3, count( $event[ 'EventCategory' ] ) );
    $this->assertEquals( 'ein', $event[ 'EventCategory' ][ 2 ][ 'name' ] );

    //do another event with another vendor
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'Vendor' ] = $vendor;

    //add vendor category called 'vendorCategoryName'
    $event->addVendorCategory( 'vendorCategoryName', $vendor['id'] );
    $event->save();
    $foo = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( 'vendorCategoryName', $vendor['id'] );
    $this->assertEquals( 'vendorCategoryName', $foo['name'] );

    //map 'foo' to new EventCategory 'bar'
    $this->mapVendorCategoryToEventCategory( $foo, 'bar' );
    $this->assertEquals( 1, count( $event[ 'EventCategory' ] ), 'Event should have one EventCategory' );
    $this->assertEquals( 'bar', $event[ 'EventCategory' ][ 0 ][ 'name' ] );
  }

  private function mapVendorCategoryToEventCategory( $vendorEventCategory, $eventCategoryName )
  {
   // $vendorEventCategoryTable = Doctrine::getTable( 'VendorEventCategory' );

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
    $this->initializeEvent();
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
    $this->initializeEvent();
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
    $this->initializeEvent();
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
    $this->initializeEvent();
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

    $this->assertEquals( count( $event[ 'EventMedia' ]) ,2 , 'All Images should be in Database' );
    $this->assertEquals( $event[ 'EventMedia' ][0][ 'url' ], $largeImageUrl , '1 is the Large Image' );

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

    $this->assertEquals( count( $event[ 'EventMedia' ]) ,2 , 'All Images should be in Database' );
    $this->assertEquals( $event[ 'EventMedia' ][1][ 'url' ], $largeImageUrl , ' 1 larger should be saved' );

   }

   /**
    * Check addMediaByUrl() get_header for array value.
    */
   public function testAddMediaByUrlMimeTypeCheck()
   {
       $this->markTestSkipped( 'Since the New Image Download Task, This part of the test cannot be tested!' );
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $event[ 'Vendor' ] = $vendor;

      // Valid URL with 302 Redirect
      $this->assertTrue( $event->addMediaByUrl( 'http://www.timeout.com/img/44494/image.jpg' ), 'addMediaByUrl() should return true if header check is valid ' );
      // 404 Error Url
      $this->assertFalse( $event->addMediaByUrl( 'http://www.toimg.net/managed/images/a10038317/image.jpg' ), 'This should fail as This is invalid URL ' );
      // Valid URL - No redirect
      $this->assertTrue( $event->addMediaByUrl( 'http://www.toimg.net/managed/images/10038317/image.jpg' ), 'This should fail as This is invalid URL ' );

   }
  /*
   * Test Media Class -> PopulateByUrl with Redirecting Image URLS
   */
  public function testMediaPopulateByUrlForRedirectingLink()
  {
      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $vendor;

      $event->addMediaByUrl( 'http://www.timeout.com/img/44494/image.jpg' ); // url Redirect to another...
      $event->addMediaByUrl( 'http://www.timeout.com/img/44484/image.jpg' ); // another url Redirect to another...
      $event->save();

      $this->assertEquals(2, $event['EventMedia']->count(), 'addMediaByUrl() Should take Both URL and Store in DB');
  }

   public function testAddVendorCategoryHTMLDecode()
   {
    $this->initializeEvent();
    $vendorCategory = "Neighborhood &amp; pubs";
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );
    $this->object->addVendorCategory( $vendorCategory, $vendor[ 'id' ] );
    $this->object->save();

    $this->assertEquals( 'Neighborhood & pubs', $this->object[ 'VendorEventCategory' ][ 'Neighborhood & pubs' ]['name'] );
    $this->assertTrue( $this->object[ 'VendorEventCategory' ][ 'Neighborhood & pubs' ]->exists() );
   }

}

?>
