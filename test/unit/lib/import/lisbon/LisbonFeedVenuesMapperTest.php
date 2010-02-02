<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LisbonFeedVenuesMapper.
 * Generated by PHPUnit on 2010-01-29 at 10:32:04.
 */
class LisbonFeedVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedVenuesMapper
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

    $this->object = new LisbonFeedVenuesMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_venues.short.xml' )
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

  public function testMapVenues()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();
    
    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertEquals( 6, $pois->count() );

    $poi = $pois[0];

    $this->assertEquals( 0,  $poi['vendor_poi_id'] );
    $this->assertEquals( '', $poi['review_date'] );
    $this->assertEquals( 'PTR', $poi['local_language'] );
    $this->assertEquals( 'Igreja da Memória', $poi['poi_name'] );
    $this->assertEquals( '', $poi['house_no'] );
    $this->assertEquals( 'Lg da Memória, ', $poi['street'] );
    $this->assertEquals( 'Lisbon', $poi['city'] );
    $this->assertEquals( '', $poi['district'] );
    $this->assertEquals( 'Portugal', $poi['country'] );
    $this->assertEquals( '', $poi['additional_address_details'] );
    $this->assertEquals( '', $poi['zips'] );
    $this->assertEquals( '', $poi['country'] );
    $this->assertEquals( 0, $poi['longitude'] );
    $this->assertEquals( 0, $poi['latitude'] );
    $this->assertEquals( '', $poi['email'] );
    $this->assertEquals( '', $poi['url'] );
    $this->assertEquals( '', $poi['phone'] );
    $this->assertEquals( '', $poi['phone2'] );
    $this->assertEquals( '', $poi['fax'] );
    $this->assertEquals( '', $poi['vendor_category'] );
    $this->assertEquals( '', $poi['keywords'] );
    $this->assertEquals( '', $poi['short_description'] );
    $this->assertEquals( '', $poi['description'] );
    $this->assertEquals( 'Tube: Saldanha, Bus: some bus, Rail: some rail', $poi['public_transport_links'] );
    $this->assertEquals( '', $poi['price_information'] );
    $this->assertEquals( '', $poi['openingtimes'] );
    $this->assertEquals( '', $poi['star_rating'] );
    $this->assertEquals( '', $poi['rating'] );
    $this->assertEquals( '', $poi['provider'] );
    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );
  }
}
?>
