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

    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' );
    $this->object = new LisbonFeedListingsMapper( $xml );
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
   * test mapListings has required fields and properties
   * @todo solve that pesky loop bug! (some elements looped twice when using $record->addProperty)
   */
  public function testMapListings()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();
    //$this->assertEquals( 3, $events->count() );

    $event = $events[0];

    $this->assertEquals( '168032', $event['vendor_event_id'] );
    $this->assertEquals( 'Arquitecto José Santa-Rita, arquitecto: Obra, marcas e identidade(s) de um percu', $event['name'] );
    $this->assertEquals( 'Constituindo, pela primeira vez, uma homenagem póstuma, esta edição do Prémio Mu', $event['short_description'] );
    $this->assertRegExp( '/^Constituindo,.*Exposições.$/', $event['description'] );
    $this->assertEquals( '', $event['booking_url'] );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( '', $event['price'] );
    $this->assertEquals( '', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    $properties = $event['EventProperty'];
    $this->assertGreaterThan( 0, $properties->count() );
  }
}
?>
