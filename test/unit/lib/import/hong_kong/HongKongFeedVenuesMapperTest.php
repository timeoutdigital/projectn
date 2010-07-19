<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Hong Kong Feed Venues Mapper import.
 *
 * @package test
 * @subpackage hong_kong.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class HongKongFeedVenuesMapperTest extends PHPUnit_Framework_TestCase
{
   /**
   * @var HongKOngFeedVenuesMapper
   */
  protected $dataMapper;

   /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    $this->moviesXml = simplexml_load_file( TO_TEST_DATA_PATH . '/hong_kong_venues_short.xml' );
    $this->dataMapper = new HongKongFeedVenuesMapper( $this->moviesXml, null );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapMovies()
  {
      $importer = new Importer();
      $importer->addDataMapper( $this->dataMapper );
      $importer->run();

      $pois = Doctrine::getTable('Poi')->findAll();

      // Check IMPORTED COUNT
      $this->assertEquals( 7, $pois->count() );

      $poi = $pois[0];

      // Verify Vendor is the Correct One
      $this->assertEquals( 23, $poi['vendor_id'] ); // 23 == Hong Kong...

      $this->assertEquals(2, $poi['vendor_poi_id']);
      $this->assertEquals('The Rotunda', $poi['name']);

      $this->assertEquals('Exchange Square', $poi['street']);
      $this->assertEquals('Central', $poi['city']);
      $this->assertEquals('Central', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      $this->assertEquals('',$poi['description']);
      $this->assertEquals('',$poi['openingtimes']);

      $this->assertEquals('',$poi['phone']);
      $this->assertEquals('',$poi['url']);

      // Category Check
      //echo 'Category Name@'.$poi[ 'LinkingVendorPoiCategory' ][0]['VendorPoiCategory']['id'].PHP_EOL;
      $this->assertGreaterThan( 0, $poi[ 'LinkingVendorPoiCategory' ]->count() );

      // Not Provided in XML
        $this->assertEquals( '', $poi['email'] );
        $this->assertEquals( '', $poi['phone2'] );
        $this->assertEquals( '', $poi['fax'] );
        $this->assertEquals( '', $poi['vendor_category'] );
        $this->assertEquals( '', $poi['keywords'] );
        $this->assertEquals( '', $poi['short_description'] );
        $this->assertEquals( '', $poi['public_transport_links'] );
        $this->assertEquals( '', $poi['price_information'] );
        $this->assertEquals( '', $poi['star_rating'] );
        $this->assertEquals( '', $poi['provider'] );
        $this->assertEquals('',$poi['vendor_category']);


      // Check for the SECOND ONE
      $poi = $pois[1];

      $this->assertEquals(3229, $poi['vendor_poi_id']);
      $this->assertEquals('Graze', $poi['name']);

      $this->assertEquals('G/F, Chaoâs Bldg, 143-145 Bonham Strand, Sheung Wan', $poi['street']);
      $this->assertEquals('Sheung Wan', $poi['city']);
      $this->assertEquals('Sheung Wan', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      $this->assertEquals('CHEAP EATS This quaint cafÃ',  mb_substr( $poi['description'],0,27, 'UTF-8'));
      $this->assertEquals('',$poi['openingtimes']);
      
      $this->assertEquals('+852 2 850 7766', $poi['phone']);
      $this->assertEquals('',$poi['url']);
      
      // Category Check
      //echo 'Category Name@'.$poi[ 'LinkingVendorPoiCategory' ][0]['VendorPoiCategory']['id'].PHP_EOL;
      $this->assertLessThan( 1, $poi[ 'LinkingVendorPoiCategory' ]->count() );

      // Not Provided in XML
        $this->assertEquals( '', $poi['email'] );
        $this->assertEquals( '', $poi['phone2'] );
        $this->assertEquals( '', $poi['fax'] );
        $this->assertEquals( '', $poi['vendor_category'] );
        $this->assertEquals( '', $poi['keywords'] );
        $this->assertEquals( '', $poi['short_description'] );
        $this->assertEquals( '', $poi['public_transport_links'] );
        $this->assertEquals( '', $poi['price_information'] );
        $this->assertEquals( '', $poi['star_rating'] );
        $this->assertEquals( '', $poi['provider'] );
        $this->assertEquals('',$poi['vendor_category']);
        
        
      // Check for the THIRD ONE
      $poi = $pois[2];

      $this->assertEquals(3585, $poi['vendor_poi_id']);
      $this->assertEquals('Pier 7', $poi['name']);

      $this->assertEquals('Shop M, Roof Viewing Deck, Pier 7, Star Ferry', $poi['street']);
      $this->assertEquals('Central', $poi['city']);
      $this->assertEquals('Central', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      //$this->assertEquals('',$poi['description']);
      $this->assertEquals('',$poi['openingtimes']);
      
      $this->assertEquals('+852 2 167 8377',$poi['phone']);
      $this->assertEquals('',$poi['url']);

      // Category Check
      $this->assertLessThan( 1, $poi[ 'LinkingVendorPoiCategory' ]->count() );

      // Not Provided in XML
        $this->assertEquals( '', $poi['email'] );
        $this->assertEquals( '', $poi['phone2'] );
        $this->assertEquals( '', $poi['fax'] );
        $this->assertEquals( '', $poi['vendor_category'] );
        $this->assertEquals( '', $poi['keywords'] );
        $this->assertEquals( '', $poi['short_description'] );
        $this->assertEquals( '', $poi['public_transport_links'] );
        $this->assertEquals( '', $poi['price_information'] );
        $this->assertEquals( '', $poi['star_rating'] );
        $this->assertEquals( '', $poi['provider'] );
        $this->assertEquals('',$poi['vendor_category']);
      
  }
  
}
?>
