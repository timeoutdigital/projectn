<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API CinemasMapper.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPIRestaurantsMapperTest extends PHPUnit_Framework_TestCase
{
  
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine_Manager::connection()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
    Doctrine::loadData( 'data/fixtures/fixtures.yml' );

    ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'london', 'language' => 'en-GB' ) );
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
   * test restaurants are mapped to pois
   */
  public function testMapPoi()
  {
    $limit = 11;
    
    $crawler = new LondonAPICrawler();
    $crawler->setLimit( $limit );
    $mapper = new LondonAPIRestaurantsMapper( $crawler );

    $importer = new Importer();
    $importer->addDataMapper( $mapper );

    $this->setExpectedException( 'Exception' );
    $importer->run();
    
    $poiResults = Doctrine::getTable('Poi')->findAll();

    $this->assertEquals( $limit, $poiResults->count() );

    $poi = $poiResults[0];

    $this->assertFalse( empty( $poi[ 'vendor_id' ] ),         'vendor_id should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'vendor_poi_id' ] ),     'vendor_poi_id should not be empty: ' . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'street' ] ),            'street should not be empty: '        . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'city' ] ),              'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'country' ] ),           'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'longitude' ] ),         'longitude should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'latitude' ] ),          'latitude should not be empty: '      . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'poi_name' ] ),          'poi_name should not be empty: '      . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'url' ] ),               'url should not be empty: '           . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'phone' ] ),             'phone should not be empty: '         . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'zips' ] ),              'zips should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'price_information' ] ), 'price_information should not be empty: ' . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'openingtimes' ] ),      'openingtimes should not be empty: '  . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'star_rating' ] ),       'star_rating should not be empty: '   . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'description' ] ),       'description should not be empty: '   . $poi[ 'url' ] );
    $this->assertEquals( empty( $poi[ 'PoiCategories' ][ 0 ][ 'name' ] ), 'restaurant', 'description should not be empty: '   . $poi[ 'url' ] );

    $this->assertGreaterThan( 0, count( $poi['PoiProperty'] ) );
  }

  /**
   * @todo test grabs all restaurants if no limit set
   */
  public function testNoLimit()
  {
    $this->markTestIncomplete();
  }
}
?>
