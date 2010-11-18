<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/londonAPICrawler.mock.php';
/**
 * Test class for London API Cinemas Mapper.
 *
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPICinemasMapperTest extends PHPUnit_Framework_TestCase
{

    private $vendor;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData( 'data/fixtures/fixtures.yml' );

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'london' );
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
    $params = array( 'datasource' => array( 'classname' => 'LondonAPICrawlerMock',
                                            'url' => 'http://api.timeout.com/v1/search.xml',
                                            'detailedurl' => TO_TEST_DATA_PATH . '/london/LondonAPICinemasMapper.xml',
                                            'apitype' => 'Cinemas' ) );
    
    $mapper = new LondonAPICinemasMapper( $this->vendor, $params );

    $importer = new Importer();
    $importer->addDataMapper( $mapper );
    $importer->run();

    $poiResults = Doctrine::getTable('Poi')->findAll();
    
    $this->assertEquals( 11, $poiResults->count() );
    
    $poi = $poiResults[0];

    $this->assertFalse( empty( $poi[ 'vendor_id' ] ),         'vendor_id should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'vendor_poi_id' ] ),     'vendor_poi_id should not be empty: ' . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'street' ] ),            'street should not be empty: '        . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'city' ] ),              'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'country' ] ),           'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'longitude' ] ),         'longitude should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'latitude' ] ),          'latitude should not be empty: '      . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'poi_name' ] ),          'poi_name should not be empty: '      . $poi[ 'url' ] );
    //$this->assertFalse( empty( $poi[ 'url' ] ),               'url should not be empty: '           . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'zips' ] ),              'zips should not be empty: '          . $poi[ 'url' ] );
    //$this->assertEquals( $poi[ 'PoiCategory' ][ 0 ][ 'name' ], 'cinema', 'category should be "bar-pub": '   . $poi[ 'url' ] );

    //$this->assertGreaterThan( 0, count( $poi['PoiProperty'] ) );
  }
  
}
