<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Barcelona Venues Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class barcelonaVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'barcelona',
      'language' => 'ca',
      'time_zone' => 'Europe/Madrid',
      'inernational_dial_code' => '+3493',
      )
    );
    $vendor->save();

    $this->vendor = $vendor;

    $this->object = new barcelonaVenuesMapper( simplexml_load_file( TO_TEST_DATA_PATH . '/barcelona/venues_trimmed.xml' ) );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapPlaces()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $pois = Doctrine::getTable( 'Poi' )->findAll();
    $this->assertEquals( 8, $pois->count() );

    $poi = $pois[0];

    $this->assertEquals( 1,  $poi['vendor_poi_id'] );
    $this->assertEquals( 'Museu de la Xocolata', $poi['name'] );
    $this->assertEquals( '36', $poi['house_no'] );
    $this->assertEquals( 'Comerç', $poi['street'] );
    $this->assertEquals( 'Barcelona', $poi['city'] );
    $this->assertEquals( 'Eixample', $poi['district'] );
    $this->assertEquals( '', $poi['additional_address_details'] );
    $this->assertEquals( 'ESP', $poi['country'] );
    $this->assertEquals( '', $poi['zips'] );
    //$this->assertEquals( '41.3872579', $poi['latitude'], "These should be gathered from Google and may fail if the Google Maps API Service is Unavailable" );
    //$this->assertEquals( '2.1818905', $poi['longitude'], "These should be gathered from Google and may fail if the Google Maps API Service is Unavailable" );
    $this->assertEquals( '', $poi['short_description'] );
    $this->assertEquals( '', $poi['description'] );
    $this->assertEquals( '3,80 €', $poi['price_information'] );
    $this->assertEquals( 'Dl. dc. dj. dv. i ds., de 10 a 19 h. Dg. i fest., de 10 a 15 h', $poi['openingtimes'] );
    $this->assertEquals( '', $poi['email'] );
    $this->assertEquals( '+3493 9 3268 7878', $poi['phone'] );
    $this->assertEquals( '', $poi['phone2'] );
    $this->assertEquals( '', $poi['fax'] );
    $this->assertEquals( 'http://www.pastisseria.com/es/PortadaMuseu', $poi['url'] );
    $this->assertEquals( "Timeout_link", $poi[ 'PoiProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.cat/barcelona/ca/s/viu-barcelona", $poi[ 'PoiProperty' ][0]['value'] );
    $this->assertEquals( 'Metro Arc de Triomf (L1)', $poi['public_transport_links'] );
    $this->assertEquals( '', $poi['rating'] );
    $this->assertEquals( '', $poi['star_rating'] );

    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );

    $this->assertGreaterThan( 1, $poi[ 'VendorPoiCategory' ]->count() );
    $this->assertEquals( "A la Ciutat", $poi[ 'VendorPoiCategory' ][0]['name'] );
    $this->assertEquals( "Museu", $poi[ 'VendorPoiCategory' ][1]['name'] );
  }


}
?>
