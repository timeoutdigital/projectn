<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
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
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    // get vendor
    $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('hong kong');

    $params = array( 'type' => 'test', 'datasource' => array( 'classname' => 'CurlMock', 'url' =>  TO_TEST_DATA_PATH . '/hong_kong/hong_kong_venues_short.xml' ) );

    $dataMapper = new HongKongFeedVenuesMapper( $vendor, $params );

    // Run Import
    $importer = new Importer();
    $importer->addDataMapper( $dataMapper );
    $importer->run();
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
      $pois = Doctrine::getTable('Poi')->findAll();

      // Check IMPORTED COUNT
      $this->assertEquals( 4, $pois->count(), 'Since the Geocode Structure chaged, we have 4 Pois in feed' );

      $poi = $pois[0];

      // Verify Vendor is the Correct One
      $this->assertEquals( 23, $poi['vendor_id'] ); // 23 == Hong Kong...

      $this->assertEquals(2, $poi['vendor_poi_id']);
      $this->assertEquals('The Rotunda', $poi['name']);

      $this->assertEquals('Exchange Square', $poi['street']);
      $this->assertEquals('Hong Kong', $poi['city']);
      $this->assertEquals('Central', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      $this->assertEquals('',$poi['description']);
      $this->assertEquals('',$poi['openingtimes']);

      $this->assertEquals('',$poi['phone']);
      $this->assertEquals('',$poi['url']);

      // Category Check
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

      $this->assertEquals(3, $poi['vendor_poi_id']);
      $this->assertEquals('Piazza Area', $poi['name']);

      $this->assertEquals('', $poi['street']);
      $this->assertEquals('Hong Kong', $poi['city']);
      $this->assertEquals('Discovery Bay', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      $this->assertEquals('',   $poi['description'] );
      $this->assertEquals('',$poi['openingtimes']);
      
      $this->assertEquals('', $poi['phone']);
      $this->assertEquals('',$poi['url']);
      
      // Category Check
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
        
        
      // Check for the THIRD ONE
      $poi = $pois[2];

      $this->assertEquals(4, $poi['vendor_poi_id']);
      $this->assertEquals('Soccer pitch of Pak Tai Temple Playground', $poi['name']);

      $this->assertEquals('Shop M, Roof Viewing Deck, Pier 7, Star Ferry', $poi['street']);
      $this->assertEquals('Hong Kong', $poi['city']);
      $this->assertEquals('Cheung Chau', $poi['district']);
      $this->assertEquals('HKG',$poi['country']);
      
      $this->assertNull($poi['rating']);
      //$this->assertEquals('',$poi['description']);
      $this->assertEquals('',$poi['openingtimes']);
      
      $this->assertEquals('',$poi['phone']);
      $this->assertEquals('',$poi['url']);

      // Category Check
      $this->assertGreaterThan(0, $poi[ 'LinkingVendorPoiCategory' ]->count() );

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

  /**
   * Validate Geocodes are extracted from mapcode tag ( from IFRAME )
   */
  public function testIframGeoCode()
  {
      // 1st and 2nd poi have Mapcode Tag in Test feed
      $poi = Doctrine::getTable('Poi')->find( 1 );

      $this->assertEquals( '22.276548', $poi['latitude']);
      $this->assertEquals( '114.16769', $poi['longitude']);
      $this->assertEquals( 'Feed', $poi['PoiMeta'][0]['value']);

      $poi = Doctrine::getTable('Poi')->find( 2 );
      $this->assertEquals( '22.28112', $poi['latitude']);
      $this->assertEquals( '114.155511', $poi['longitude']);
      $this->assertEquals( 'Feed', $poi['PoiMeta'][0]['value']);

      $poi = Doctrine::getTable('Poi')->find( 3 );
      $this->assertEquals( null, $poi['latitude']);
      $this->assertEquals( null, $poi['longitude']);
      $this->assertEquals( 0 , $poi['PoiMeta']->count() );
  }

  /**
  * Hong kong has recently changed it's mapcode taga nd now provide geocodes comma separated,
  * This test to ensure that no other changes to structure were made
  */

  public function testGeocodeChaneAndudpatedFeed()
  {
      $poi = Doctrine::getTable( 'Poi' )->find( 4 );
      $this->assertEquals( 23, $poi['vendor_poi_id']);
      $this->assertEquals( '8 Happiness', $poi['poi_name']);
      $this->assertEquals( '22.27825523', $poi['latitude'] );
      $this->assertEquals( '114.182689', $poi['longitude'] );
  }
  
}
?>
