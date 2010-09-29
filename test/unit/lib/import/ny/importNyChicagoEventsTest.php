 <?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

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

      // Load the XML into MAPPER and IMPORT
      $params = array( 'type' => 'poi', 'ftp' =>
                                        array( 'classname' => 'FTPClientMock', 'ftp' => 'ftp.timeoutny.com', 'username' => 'test', 'password' => 'test', 'dir' => '/', 'file' => TO_TEST_DATA_PATH.'/tony_leo_test_correct.xml' ) );

      // Run POI import
      $importer = new Importer();
      $importer->addDataMapper( new nyEventsAndPoiMapper( $this->vendorObj, $params ) );
      $importer->run();

      // Run Event Mapper
      $params['type'] = 'event';
      $importer = new Importer();
      $importer->addDataMapper( new nyEventsAndPoiMapper( $this->vendorObj, $params ) );
      $importer->run();

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
   * Pois do not have categories. As we import events, we need to copy
   * the event's categories over to its related POI
   */
  public function testPoisAreAttachedWithCategoriesOfRelatedEvents()
  {
    $this->markTestSkipped();
    //not required anymore
    $testXml = $this->loadTestFeedFrom( TO_TEST_DATA_PATH . '/ny_poi_gets_event_categories.xml' );
    $importer = new importNyChicagoEvents( $testXml, $this->vendorObj );
    $importer->insertEventCategoriesAndEventsAndVenues();

    $eventTable = Doctrine::getTable( 'Event' );
    $poiTable = Doctrine::getTable( 'Poi' );

    $this->assertEquals( 3, $eventTable->count() );
    $this->assertEquals( 2, $poiTable->count() );

    $firstPoi = $poiTable->findOneById( 1 );
    $this->assertEquals( 0, count( $firstPoi[ 'VendorPoiCategory' ] ) );
  }

  private function loadTestFeedFrom( $sourceFile )
  {
    $xmlFeed  = new processNyXml( $sourceFile );

    if( !$xmlFeed->getXml() )
      $this->fail( 'Could not find test file:' . $testData );

    $xmlFeed->setEvents('/body/event')->setVenues('/body/address');

    return $xmlFeed;
  }

   /**
   * testInsertPoi
    *
    * @todo Create Regreshion test
   */
  public function testInsertPoi()
  {

    $poiObj = Doctrine::getTable('Poi')->findByPoiName('Zankel Hall (at Carnegie Hall)');

    $this->assertEquals( 1, count( $poiObj ) );
  }

  /**
   * @todo Implement the test for the start date.
   */
  public function testStartDate()
  {
    $this->markTestSkipped();
  }


  /**
   *
   */
  public function testInsertEventAndEventOccurrences()
  {

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    $this->assertTrue( $eventObj instanceof Event, 'And event should be returned.' );

    $occurrenceObj = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( "311131_103532_20091219180000" );

    $this->assertEquals( 1, $eventObj['EventOccurrence']->count(), 'there should be one occurrence on the event.'  );
  }


  /*
   * test if price information is appended
   */
  public function testInsertPriceInformationProperty()
  {

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


  /**
   *
   */
  public function testInsertEventProperty()
  {

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

  /**
   * test if attribute is appended
   *
   */
  public function testCriticsPicksPropertyOnEvent()
  {

    $eventObj = Doctrine::getTable('Event')->findOneByName('Rien Que Les Heures');

    //Test that the Critics_choice is normalized to this form
    $this->assertEquals( 'Critics_choice', $eventObj[ 'EventProperty' ][ 2 ][ 'lookup' ], "Use 'Critics_choice' instead of 'Critic's Pick' or whatever else.");
  }

  /*
  * test testCategoryIfVendorEventCategorIsSuccessfullyAppended
  */
  public function testCategoryIfVendorEventCategorIsSuccessfullyAppended()
  {
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

      $poiTable = Doctrine::getTable('Poi');

      $this->assertEquals( 8, $poiTable->count(), 'There should only be 8 poi imported' );

      $venueId = 103532;
      $poi = $poiTable->findOneByVendorIdAndVendorPoiId( $this->vendorObj['id'], $venueId );

      $this->assertEquals( 1, count( $poi['VendorPoiCategory'] ) );

      $this->assertEquals( 'Shops', $poi['VendorPoiCategory'][0]['name'] );

  }

  public function testPoiGetsEventsVendorPoiCategoryIfItDoesntHaveAny()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();
      Doctrine::loadData('data/fixtures');
      // Insert Only POI
      // Load the XML into MAPPER and IMPORT
      $params = array( 'type' => 'poi', 'ftp' =>
                                        array( 'classname' => 'FTPClientMock', 'ftp' => 'ftp.timeoutny.com', 'username' => 'test', 'password' => 'test', 'dir' => '/', 'file' => TO_TEST_DATA_PATH.'/tony_leo_test_correct.xml' ) );

      // Run POI import
      $importer = new Importer();
      $importer->addDataMapper( new nyEventsAndPoiMapper( $this->vendorObj, $params ) );
      $importer->run();

      $venueId = 101130;
      $poiObj = Doctrine::getTable("Poi")->findOneByVendorIdAndVendorPoiId( $this->vendorObj['id'], $venueId );
      //poi doesn't have category
      $this->assertEquals( 0, count( $poiObj['VendorPoiCategory'] ) );

      // Now Insert Event and Add Category to POI

      // Run Event Mapper
      $params['type'] = 'event';
      $importer = new Importer();
      $importer->addDataMapper( new nyEventsAndPoiMapper( $this->vendorObj, $params ) );
      $importer->run();
      
      //after saving an event happening in this poi, poi should have the event's category
      $this->assertEquals( 1, count( $poiObj['VendorPoiCategory'] ) );
      $this->assertEquals( 'Film | Art-house & indie cinema',  $poiObj['VendorPoiCategory'][0]['name'] );

  }


}
?>
