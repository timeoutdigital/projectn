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
   * @var barcelonaVenuesMapper
   */
  protected $venuesMapper;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city'      => 'barcelona',
      'language'  => 'es',
      'time_zone' => 'Europe/Madrid',
      )
    );
    $vendor->save();

    $this->vendor = $vendor;

    $this->object = new barcelonaVenuesMapper( simplexml_load_file( TO_TEST_DATA_PATH . '/barcelona/venues.xml' ) );
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
    $this->assertEquals( 3, $pois->count() );

    $this->assertEquals( 1, $pois[0]['VendorPoiCategory']->count() );
    $this->assertEquals( 2, $pois[1]['VendorPoiCategory']->count() );

    $this->assertEquals( 'http://www.timeout.cat/barcelona/ca/s/viu-barcelona',
                         $pois[0]->getTimeoutLinkProperty(),
                         'Should have a timeout link as a property');

    $this->assertEquals( 'http://www.pastisseria.com/es/PortadaMuseu',
                         $pois[0]['url'],
                         'Url should come from url tag in xml, not timeout_url'
                        );

    $this->assertEquals( 2, 
                         $pois[0]['VendorPoiCategory']->count(), 
                         'First poi from fixure should have 2 categories' );
  }
}
?>
