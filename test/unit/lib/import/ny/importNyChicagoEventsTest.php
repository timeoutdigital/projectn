<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';


/**
 * Test class for importNy.
 *
 * @package test
 * @subpackage ny.import.lib
 *
 *
 * @author Tim Bowler <timbowler@timeout.com>
 *
 * @copyright Timeout Communications Ltd
 *
 */
class importNyTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var importNyChicagoEvents
   */
  protected $object;

  protected $xmlObj;

  protected $vendorObj;

  protected $categoryMap;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {

    try {

      ProjectN_Test_Unit_Factory::createDatabases();

      Doctrine::loadData('data/fixtures');
      $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');

      $poiCategoryObj = new PoiCategory();
      $poiCategoryObj[ 'name' ] = 'theatre-music-culture';
      $poiCategoryObj->save();

      $this->xmlObj = new processNyXml( TO_TEST_DATA_PATH.'/tony_leo_test_correct.xml' );
      $this->xmlObj->setEvents('/body/event')->setVenues('/body/address');

      $this->object = new importNyChicagoEvents( $this->xmlObj, $this->vendorObj );

    }
    catch( Exception $e )
    {
      echo $e->getMessage();
    }

    $this->categoryMap = new CategoryMap( false );

  }


  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    //Close DB connection
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }


   /**
   * testInsertPoi
    *
    * @todo Create Regreshion test
   */
  public function testInsertPoi()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );
    $this->object->_poiLoggerObj->save();

    $poiObj = Doctrine::getTable('Poi')->findByPoiName('Zankel Hall (at Carnegie Hall)');

    $this->assertEquals( 1, count( $poiObj ) );
  }

  public function testStartDate()
  {
    $this->markTestSkipped();
  }


  /**
   *
   */
  public function testInsertEventAndEventOccurrences()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    $this->assertTrue( $eventObj instanceof Event, 'And event should be returned.' );

    $this->assertEquals( 1, $eventObj['EventOccurrence']->count(), 'Should be one occurrence on the event.'  );
  }


  /*
   * test if price information is appended
   */
  public function testInsertPriceInformationProperty()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $poiObj = Doctrine::getTable('Poi')->findOneByPoiName('Zankel Hall (at Carnegie Hall)');

    $passed = false;

    foreach( $poiObj['PoiProperty'] as $poiPropertyObj )
    {
      if ( $poiPropertyObj[ 'lookup' ] == 'price_general_remark' && 'children under 5 not admitted.' == $poiPropertyObj[ 'value' ] )
      {
        $passed = true;
        break;
      }
    }

    $this->assertTrue( $passed );

    $passed = false;

    foreach( $poiObj['PoiProperty'] as $poiPropertyObj )
    {
      if ( $poiPropertyObj[ 'lookup' ] == 'price' && 'USD 12.50' == $poiPropertyObj[ 'value' ] )
      {
        $passed = true;
        break;
      }
    }

    $this->assertTrue( $passed );
  }

  /*
   * Test catgegory property for poi
   *
   * <category_combi id="329">
   *   <category1 id="279">Film</category1>
   *   <category2 id=""/>
   *   <category3 id=""/>
   * </category_combi>
   *
  */
  public function testPoiCategoryProperty()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $poiObj = Doctrine::getTable('Poi')->findOneByPoiName('Zankel Hall (at Carnegie Hall)');

    foreach( $poiObj['PoiProperty'] as $eventPropertyObj )
    {
      if ( $eventPropertyObj[ 'lookup' ] == 'category' )
      {
        $this->assertEquals( 'Film', $eventPropertyObj[ 'value' ] );
        break;
      }
    }

  }

  public function testInsertEventProperty()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    foreach( $eventObj['EventProperty'] as $eventPropertyObj )
    {
      if ( $eventPropertyObj[ 'lookup' ] == 'prices' )
      {
        $this->assertEquals( '$10', $eventPropertyObj[ 'value' ] );
        break;
      }
    }
  }

  public function testContactBlurb()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    // url
    $this->assertEquals( 'http://theatermania.com', $eventObj[ 'url' ] );

    // email
    foreach( $eventObj['EventProperty'] as $eventPropertyObj )
    {
      if ( $eventPropertyObj[ 'lookup' ] == 'email' )
      {
        $this->assertEquals( 'steve@timeout.com', $eventPropertyObj[ 'value' ] );
        break;
      }
    }

    // phone
    foreach( $eventObj['EventProperty'] as $eventPropertyObj )
    {
      if ( $eventPropertyObj[ 'lookup' ] == 'phone' )
      {
        $this->assertEquals( '212-352-3101', $eventPropertyObj[ 'value' ] );
        break;
      }
    }

  }

  /*
   * test if attribute is appended
   */
  public function testCriticsPicksPropertyOnEvent()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    //Critic\'s Picks
    $this->assertEquals( 'Critic\'s Picks', $eventObj[ 'EventProperty' ][ 2 ][ 'lookup' ]);
  }

  /*
  * test testCategoryIfVendorEventCategorIsSuccessfullyAppended
  */
  public function testCategoryIfVendorEventCategorIsSuccessfullyAppended()
  {
    $venuesArray = $this->xmlObj->getVenues();
    $this->object->insertPoi( $venuesArray[ 0 ] );

    $eventsArray = $this->xmlObj->getEvents();
    $this->object->insertEvent( $eventsArray[ 0 ] );

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    $this->assertEquals( 'Comedy', $eventObj['VendorEventCategory'][ 0 ][ 'name' ] );
  }

  /*
   * Test if the poi categories get mapped correctly
   *
   * @todo re-implement
   */
  public function testPoiCategoryMapShops()
  {
    $vendorCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'VendorPoiCategory' ) );
    $vendorCategoriesArray[] = Doctrine::getTable( 'VendorPoiCategory' )->findOneByVendorIdAndName( 1, 'Not found' );
    $vendorCategoriesArray[] = Doctrine::getTable( 'VendorPoiCategory' )->findOneByVendorIdAndName( 1, 'Shops' );

    $mappedCategoriesObject = $this->categoryMap->mapCategories(  $this->vendorObj, $vendorCategoriesArray, 'Poi', 'theatre-music-culture' );

    $this->assertTrue( $mappedCategoriesObject instanceof Doctrine_Collection );

    $this->assertEquals( 'shop', $mappedCategoriesObject[ 0 ][ 'name' ] );

    $this->assertEquals( 1, count( $mappedCategoriesObject ) );

  }

  /*
   * Test if the event categories get mapped correctly
   */
  public function testEventCategoryMapComedy()
  {
    $vendorCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'VendorEventCategory' ) );
    $vendorCategoriesArray[] = Doctrine::getTable( 'VendorEventCategory' )->findOneByVendorIdAndName( 1, 'Not found' );
    $vendorCategoriesArray[] = Doctrine::getTable( 'VendorEventCategory' )->findOneByVendorIdAndName( 1, 'Comedy' );

    $mappedCategoriesObject = $this->categoryMap->mapCategories( $this->vendorObj, $vendorCategoriesArray, 'Event' );

    $this->assertTrue( $mappedCategoriesObject instanceof Doctrine_Collection );

    $this->assertEquals( 'theater', $mappedCategoriesObject[ 0 ][ 'name' ] );

    $this->assertEquals( 1, count( $mappedCategoriesObject ) );
  }


  public function testPoisHaveCategories()
  {
      $venuesArray = $this->xmlObj->getVenues();
      $testVenue = $venuesArray[0];
      
      
      $venueId = 103532;
      $this->assertEquals( $venueId, (int) $testVenue['id'], 'The venue we are working on is has id '.$venueId );
      
      $this->object->insertPoi( $testVenue );
      $poiTable = Doctrine::getTable('Poi');
     

      $this->assertEquals( 1, $poiTable->count(), 'There should only be one poi imported' );

      $poi = $poiTable->findOneByVendorIdAndVendorPoiId( $this->vendorObj['id'], $venueId );

      $this->assertEquals( 0, count( $poi['VendorPoiCategory'] ), 'The poi should only no vendor category as there is no category there' );

      //The Poi gets its categories from the event if it doesn't have any
      $eventsArray = $this->xmlObj->getEvents();
      $testEvent = $eventsArray[0];


      $this->object->insertEvent($testEvent);
      $poiObj = Doctrine::getTable("Poi")->findOneByVendorIdAndVendorPoiId( $this->vendorObj['id'], $venueId );
      $this->assertEquals( 1,count($poiObj['VendorPoiCategory']->toArray()), 'The poi should now have a category based on event category' );

  }
}
?>
