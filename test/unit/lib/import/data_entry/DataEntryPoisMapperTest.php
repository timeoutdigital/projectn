<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of data entry Feed Venues Mapper import.
 *
 * @package test
 *
 * @author emre basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DataEntryPoisMapperTest extends PHPUnit_Framework_TestCase
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
      'city' => 'barcelona',
      'language' => 'ca',
      'time_zone' => 'Europe/Madrid',
      'inernational_dial_code' => '+3493',
      )
    );
    $vendor->save();

    $this->vendor = $vendor;

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;
    DataEntryImportManager::setImportDir( $importDir );

    DataEntryImportManager::importPois( );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }


  public function testMapping()
  {
    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertGreaterThan( 1, $pois->count() );
    $this->assertLessThan( 7, $pois->count() );
    $poi = $pois[0];

    $this->assertEquals( 51233,  $poi['vendor_poi_id'] );
    $this->assertEquals( '', $poi['review_date'] );
    $this->assertEquals( 'ca', $poi['local_language'] );
    $this->assertEquals( 'Museu Arqueologia de Catalunya', $poi['poi_name'] );
    $this->assertEquals( '39-41', $poi['house_no'] );
    $this->assertEquals( 'Pg. Santa Madrona', $poi['street'] );
    $this->assertEquals( 'Barcelona', $poi['city'] );
    $this->assertEquals( 'Eixample', $poi['district'] );
    $this->assertEquals( 'ESP', $poi['country'] );
    $this->assertEquals( 'abcdefg', $poi['zips'] );
    $this->assertNotNull( $poi['longitude'] );
    $this->assertNotNull( $poi['latitude'] );
    $this->assertEquals( '41.37023910', $poi['latitude'] );
    $this->assertEquals( 'foo@hotmail.com', $poi['email'] );
    $this->assertEquals( 'http://www.mac.cat', $poi['url'] );
    $this->assertEquals( '+3493 9 3424 6577', $poi['phone'] );
    $this->assertEquals( '+3493 9 3424 6578', $poi['phone2'] );
    $this->assertEquals( '+3493 9 3424 6579', $poi['fax'] );
    $this->assertEquals( 'A la Ciutat', $poi['VendorPoiCategory'][0]['name'] );
    $this->assertEquals( '', $poi['keywords'] );
    $this->assertEquals( 'a very short description', $poi['short_description'] );
    $this->assertEquals( 'foo bar foo bar foo foo bar23333', $poi['description'] );
    $this->assertEquals( 'Metro Espanya (L1-L3-FGC)', $poi['public_transport_links'] );
    $this->assertEquals( 'not free', $poi['price_information'] );
    $this->assertEquals( 'De dt. a ds., de 9.30 a 19 h. Dg. i fest., de 10 a 14.30 h', $poi['openingtimes'] );
    $this->assertEquals( 0, $poi['star_rating'] );
    $this->assertEquals( 0, $poi['rating'] );
    $this->assertEquals( '', $poi['provider'] );
    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );

    $this->assertGreaterThan( 0, $poi[ 'PoiProperty' ]->count() );
    $this->assertEquals( 'http://www.timeout.cat/barcelona/ca/s/viu-barcelona', $poi[ 'PoiProperty' ][0] ['value']  );
    $this->assertEquals( 'Timeout_link', $poi[ 'PoiProperty' ][0] ['lookup']  );
  }
}
