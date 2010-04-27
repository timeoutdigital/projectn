<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London Importer.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonDatabaseEventsAndVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LondonImporter
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases( );

    // load projectn data
    Doctrine::loadData( 'data/fixtures/fixtures.yml' );

    // load london data
    Doctrine::loadData( dirname( __FILE__ ) . '/../../../../../plugins/toLondonPlugin/data/fixtures/searchlight_london.yml' );
    
    $importer = new Importer();
    $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper() );
    $importer->run();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases( );
  }

  public function testImportDoesNotStopIfPoiFailsToSave()
  {
    //don't need to do anything, the import will stop in setup()
  }

  /**
   *
   * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
   */
  public function testEventsHaveCategories()
  {
    //event1
    $event1 = Doctrine::getTable( 'Event' )->findOneById( 1 );
    $this->assertEquals( 'Dummy Title 1', $event1[ 'name' ] );

    $this->assertEquals( 1, count( $event1[ 'VendorEventCategory' ] ) );
    $this->assertEquals( 'Root', $event1[ 'VendorEventCategory' ][ 'Root' ][ 'name' ] );

    //event2
    $event2 = Doctrine::getTable( 'Event' )->findOneById( 2 );
    $this->assertEquals( 'Dummy Title 2', $event2[ 'name' ] );

    $this->assertEquals( 1, count( $event2[ 'VendorEventCategory' ] ) );
    $this->assertEquals( 'Root | Root Child 1', $event2[ 'VendorEventCategory' ][ 'Root | Root Child 1' ][ 'name' ] );
  }

  /**
   *
   * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
   */
  public function testProcessEventsImportedVenue()
  {
    $this->assertEquals( 4, Doctrine::getTable('Poi')->count() );
   
    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( '1' );
    //$poi = Doctrine::getTable( 'Poi' )->findAll();
    
    $this->assertTrue( $poi instanceof Doctrine_Record );

    $this->assertEquals( 'Dummy Building Name 1', $poi[ 'house_no' ]  );

    //tests ', London ' is removed from end of address/street
    $this->assertEquals( 'Dummy Address 1',       $poi[ 'street' ] );

    $this->assertEquals( 'London',                $poi[ 'city' ] );
    $this->assertEquals( '',                      $poi[ 'district' ] );
    $this->assertEquals( 'GBR',                   $poi[ 'country' ] );
    $this->assertEquals( '',                      $poi[ 'additional_address_details' ] );
    $this->assertEquals( 'Dummy Postcode 1',      $poi[ 'zips' ] );
    //$this->assertEquals( '',                      $poi[ 'extension' ] );
    $this->assertEquals( '51.0000000',            $poi[ 'latitude' ] );
    $this->assertEquals( '-0.10000000',           $poi[ 'longitude' ] );
    $this->assertEquals( 'Dummy Email 1',         $poi[ 'email' ] );
    $this->assertEquals( 'http://timeout.com',    $poi[ 'url' ] );
    $this->assertEquals( '+44 207 458 4569',     $poi[ 'phone' ] );
    $this->assertEquals( '',                      $poi[ 'phone2' ] );
    $this->assertEquals( '',                      $poi[ 'fax' ] );
    $this->assertEquals( '',                      $poi[ 'vendor_category' ] );
    $this->assertEquals( '',                      $poi[ 'keywords' ] );
    $this->assertEquals( '',                      $poi[ 'short_description' ] );
    $this->assertEquals( '',                      $poi[ 'description' ] );
    $this->assertEquals( 'Dummy Travel 1',        $poi[ 'public_transport_links' ] );
    $this->assertEquals( '',                      $poi[ 'price_information' ] );
    $this->assertEquals( 'Dummy Opening Times 1', $poi[ 'openingtimes' ] );
    $this->assertEquals( '',                      $poi[ 'star_rating' ] );
    $this->assertEquals( '',                      $poi[ 'rating' ] );
    $this->assertEquals( '',                      $poi[ 'provider' ] );

    //$this->assertEquals( 'theatre-music-culture', $poi[ 'PoiCategory' ][ 0 ][ 'name' ] );
  }

  public function testEventAndOccurrencesNotSavedIfPoiNotSaved()
  {
    $poiTable = Doctrine::getTable( 'Poi' );
    $this->assertEquals( 4, $poiTable->count() );
  }

  public function testVenueCategoryAssignment()
  {
    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( '1' );
    $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'First POI should have 1 category' );
    $this->assertEquals( 'Root', $poi['VendorPoiCategory'][0]['name'] );

    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( '2' );
    $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Second POI should have 1 category' );
    $this->assertEquals( 'Root | Root Child 1', $poi['VendorPoiCategory'][0]['name'] );

    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( '3' );
    $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Third POI should have 1 category' );
    $this->assertEquals( 'Root | Root Child 1 | Child 1 Child 1', $poi['VendorPoiCategory'][0]['name'] );
  }

  /**
   *
   * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
   */
  public function testProcessEventsImportedEvent()
  {
    
    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 1 );

    $this->assertTrue( $event instanceof Doctrine_Record );

    $this->assertEquals( 'Dummy Title 1', $event[ 'name' ]  );
  }

  /**
   *
   * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
   */
  public function testProcessEventsImportedOccurrence()
  {
     
    $occurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( 1 );

    $this->assertTrue( $occurrence instanceof Doctrine_Record );

    $this->assertEquals( date( 'Y-m-d' ), $occurrence[ 'start_date' ]  );

    $zone = new DateTimeZone( 'Europe/London' );
    $datetime = new DateTime( 'now', $zone );
    $offset = $datetime->format( 'P' );
    $this->assertEquals( $offset, $occurrence[ 'utc_offset' ]  );

    $occurrence2 = Doctrine::getTable( 'EventOccurrence' )->findOneById( 2 );

    $this->assertTrue( $occurrence2 instanceof Doctrine_Record );
    $this->assertEquals( $offset, $occurrence2[ 'utc_offset' ]  );
  }

}
