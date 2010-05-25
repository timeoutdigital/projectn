<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Russia Feed Places Mapper import.
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
class RussiaFeedPlacesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var RussiaFeedPlacesMapper
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
      'city' => 'moscow',
      'language' => 'ru',
      'time_zone' => 'Europe/Moscow',
      )
    );
    $vendor->save();
    $this->vendor = $vendor;

    $this->object = new RussiaFeedPlacesMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/moscow_places.short.xml' ),
      null,
      "moscow"
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

  public function testMapPlaces()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();
    
    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertEquals( 3, $pois->count() );
    $poi = $pois[0];

    $this->assertEquals( 1,  $poi['vendor_poi_id'] );
    $this->assertEquals( 'Одежда из Европы', $poi['name'] );
    $this->assertEquals( 'Таллиннская, 17, корп. 1', $poi['street'] );
    $this->assertEquals( 'Москва', $poi['city'] );
    $this->assertEquals( 'RUS', $poi['country'] );
    $this->assertEquals( '55.8000225', $poi['latitude'] );
    $this->assertEquals( '37.4029402', $poi['longitude'] );
    $this->assertEquals( 'Этот магазин известен давно. Здесь много вещей неизвестных марок, на которых значится - Made in Turkey, Made in Ukraine.., они-то и представляют основной интерес.', $poi['short_description'] );
    $this->assertEquals( '<p>Этот магазин известен давно. Здесь много вещей неизвестных марок', substr( $poi['description'], 0 ,122 ) );
    $this->assertEquals( '+44 750 3553', $poi['phone'] );
    $this->assertEquals( '2010-04-06 15:28:25', $poi['review_date'] );
    $this->assertEquals( 'Строгино', $poi['public_transport_links'] );
    $this->assertNull( $poi['rating'] );
    $this->assertNull( $poi['star_rating'] );
    
    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );
    
    $this->assertGreaterThan( 0, $poi[ 'VendorPoiCategory' ]->count() );
    $this->assertEquals( "одежда | мода", $poi[ 'VendorPoiCategory' ][0]['name'] );

    $this->assertEquals( "Timeout_link", $poi[ 'PoiProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/fashion/place/1/", $poi[ 'PoiProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $poi[ 'PoiMedia' ]->count() );
    $this->assertEquals( "http://pix.timeout.ru/2000.jpeg", $poi[ 'PoiMedia' ][0]['url'] );
  }
}
?>
