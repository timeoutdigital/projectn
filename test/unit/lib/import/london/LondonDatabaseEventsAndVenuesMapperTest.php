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

  protected $vendor;

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

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('london');

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
   * Test to see that media file(s) imports correctly.
   * At time of writing, fixtures conatins one valid venue media url.
   */
  public function testMediaImports()
  {
      // Run London Import
      $this->runImport( 'poi');

      $pois = Doctrine::getTable( 'PoiMedia' )->findAll();
     #568 Fix unite Test, Since Image download, we are adding All images to Database First
     $this->assertGreaterThan(1, $pois->count(), "Since Image download Task, Media should have All the Images" );
  }

  /**
   *
   * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
   */
  public function testEventsHaveCategories()
  {
      // Run London Import
      $this->runImport( 'event');
      
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
      // Run London Import
      $this->runImport( 'poi');
      
    $this->assertEquals( 4, Doctrine::getTable('Poi')->count() );

    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( '1' );

    $this->assertTrue( $poi instanceof Doctrine_Record );

    $this->assertEquals( 'Dummy Building Name 1', $poi[ 'house_no' ]  );
    $this->assertEquals( 'Dummy Address 1',       $poi[ 'street' ] );
    $this->assertEquals( 'London',                $poi[ 'city' ] );
    $this->assertEquals( '',                      $poi[ 'district' ] );
    $this->assertEquals( 'GBR',                   $poi[ 'country' ] );
    $this->assertEquals( '',                      $poi[ 'additional_address_details' ] );
    $this->assertEquals( 'Dummy Postcode 1',      $poi[ 'zips' ] );
    //$this->assertEquals( '',                      $poi[ 'extension' ] );
    $this->assertEquals( '51.0000000',            $poi[ 'latitude' ] );
    $this->assertEquals( '-0.10000000',           $poi[ 'longitude' ] );
    $this->assertEquals( 'dummy@foobar.com',         $poi[ 'email' ] );
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

  /**
   * @see unfuddled ticket #250
   */
  public function testCommaLondonNotInEndOfAddressField()
  {
      // Run London Import
      $this->runImport( 'poi');
      
    $pois = Doctrine::getTable('Poi');
    $this->assertEquals( 4, $pois->count() );
    foreach( $pois as $poi )
    {
      $this->assertRegexp( '/Dummy Address [1-3]/', $poi['street'] );
    }
  }

  public function testEventAndOccurrencesNotSavedIfPoiNotSaved()
  {
      // Run London Import
      $this->runImport( 'poi');

    $poiTable = Doctrine::getTable( 'Poi' );
    $this->assertEquals( 4, $poiTable->count() );
  }

  public function testVenueCategoryAssignment()
  {
      // Run London Import
      $this->runImport( 'poi');
      
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
      // Run London Import
      $this->runImport( 'event');

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
      // Run London Import
      $this->runImport( 'poi');
      $this->runImport( 'event');
      $this->runImport( 'event-occurrence');
      
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


  private function runImport( $type )
  {
      // Run London Import
      $importer = new Importer();
      $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( $this->vendor, array( 'type' => $type )) );
      $importer->run();
  }
  public function testImportWithTypeEvent()
  {
    $this->runImport( 'event' );
    $events = Doctrine::getTable( 'Event' );
    $this->assertEquals( 4, $events->count() );
  }

  public function testImportWithTypeEventOccurrence()
  {
    $this->runImport( 'poi' );
    $this->runImport( 'event' );
    $this->runImport( 'event-occurrence' );
    $occurrences = Doctrine::getTable( 'EventOccurrence' );
    $this->assertEquals( 4, $occurrences->count() );
  }

}
