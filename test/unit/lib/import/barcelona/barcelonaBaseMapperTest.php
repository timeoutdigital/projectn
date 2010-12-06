<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of Barcelona Events Mapper import.
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
class barcelonaBaseMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $params;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');
    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('barcelona');
    $this->params = array( 'type' => 'base', 'curl' => array( 'classname' => 'CurlMock', 'src' => '' ) );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testExtractCategories()
  {
      $this->params['curl']['src'] = TO_TEST_DATA_PATH . '/barcelona/basemapper_extract_category.xml';

      $importer = new Importer();
      $importer->addDataMapper( new mockBarcelonaMapper( $this->vendor, $this->params ) );
      $importer->run();

      $pois = Doctrine::getTable( 'Poi' )->findAll();
      $poi = $pois[ 0 ];

      $this->assertEquals( 2, count( $poi['VendorPoiCategory'] ) );
      $this->assertEquals( 'Foo', $poi['VendorPoiCategory'][0]['name'] );
      $this->assertEquals( 'Foo2 | Bar2', $poi['VendorPoiCategory'][1]['name'] );
  }
}

class mockBarcelonaMapper extends barcelonaBaseMapper
{
    public function mapMock()
    {
        for( $i=0, $mockElement = $this->xml->mock[ 0 ]; $i<$this->xml->mock->count(); $i++, $mockElement = $this->xml->mock[ $i ] )
        {
            $poi = ProjectN_Test_Unit_Factory::get( 'Poi' ); // Can use any Model
            $poi[ 'Vendor' ] = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage( 'barcelona', 'ca' );

            // Delete VendorPoiCategory from Factory
            $poi['VendorPoiCategory']->delete();

            // Add the Categories
            $cats = $this->extractCategories( $mockElement );
            foreach( $cats as $cat ) $poi->addVendorCategory( $cat );

            // Save
            $poi->save();
        }
    }
}
?>
