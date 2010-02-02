<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LisbonFeedListingsMapper.
 * Generated by PHPUnit on 2010-02-01 at 10:20:26.
 */
class LisbonFeedListingsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedListingsMapper
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'Lisbon',
      'language' => 'pt'
      )
    );
    $vendor->save();
    $this->vendor = $vendor;

    $this->object = new LisbonFeedListingsMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' )
    );
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
   * @todo Implement testMapVenues().
   */
  public function testMapVenues()
  {
//    $importer = new Importer();
//    $importer->addDataMapper( $this->object );
//    $importer->run();
//
//    $events = Doctrine::getTable('Event')->findAll();
//    $this->assertEquals( 3, $events->count() );
  }
}
?>