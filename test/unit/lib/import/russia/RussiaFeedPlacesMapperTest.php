<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
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
    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'moscow' );

  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  private function _getParams( $filename, $index = 1 )
    {
        return array(
            'type' => 'poi',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/russia/' . $filename
             ),
            'split' => array(
                'chunk' => 2,
                'index' => $index
            ),
            'phone' => array (
              'areacode' => '495'
            ),
        );
    }

  public function testMapPlaces()
  {
    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $this->_getParams( 'moscow_places.short.xml' ) ) );
    $importer->run();

    // At this point, Only first half should have inserted ( which is 2 (because of ceil))
    $this->assertEquals( 2 , Doctrine::getTable('Poi')->findAll()->count() );
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $this->_getParams( 'moscow_places.short.xml', 2 ) ) ); // Run the seconds half
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
    $this->assertEquals( '+7 495 750 3553', $poi['phone'] );
    $this->assertEquals( '2010-04-06 15:28:25', $poi['review_date'] );
    $this->assertEquals( 'Строгино', $poi['public_transport_links'] );
    $this->assertNull( $poi['rating'] );
    $this->assertEquals( 0, $poi['star_rating'] );

    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );

    $this->assertGreaterThan( 0, $poi[ 'VendorPoiCategory' ]->count() );
    $this->assertEquals( "одежда | мода", $poi[ 'VendorPoiCategory' ][0]['name'] );

    $this->assertEquals( "Timeout_link", $poi[ 'PoiProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/fashion/place/1/", $poi[ 'PoiProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $poi[ 'PoiMedia' ]->count() );
    $this->assertEquals( "http://pix.timeout.ru/2000.jpeg", $poi[ 'PoiMedia' ][0]['url'] );

    // description / short description remove leading newline character
    $poi = $pois[1];
    $this->assertEquals( "Большая игровая",  $poi['short_description'] );
    $this->assertEquals( '', $poi['description'] );

  }

  public function testQuotRemovedFromName()
  {

    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $this->_getParams( 'moscow_place_with_quot_in_name.xml' ) ) );
    $importer->run();

    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertEquals( 1, $pois->count() );
    $poi = $pois[0];

    $this->assertFalse( strpos( $poi[ 'poi_name' ], "&quot;" ) );
  }

  public function testShortDescriptionDoesNotHaveHTMLTags()
  {
      $params = $this->_getParams( 'moscow_places.short.xml' );
      $params['split'] = null;
      
    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $params ) );
    $importer->run();

    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertEquals( 3, $pois->count() );
    $poi = $pois[2];

    $this->assertEquals( 'Большая игровая комната, двухуровневый лабиринт, кафе, мульткафе, по субботам с 16.00 до18.00 пираты превращают детей в сказочных персонажей', $poi['short_description'] );
  }

  public function testPhoneNumberFixer()
  {
    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $this->_getParams( 'moscow_places.short.phonenumberfixer.xml' ) ) );
    $importer->run();

    // At this point, Only first half should have inserted ( which is 2 (because of ceil))
    $this->assertEquals( 2 , Doctrine::getTable('Poi')->findAll()->count() );
    $importer->addDataMapper( new RussiaFeedPlacesMapper( $this->vendor, $this->_getParams( 'moscow_places.short.phonenumberfixer.xml', 2 ) ) ); // Run the seconds half
    $importer->run();

    $this->assertEquals( 3, Doctrine::getTable( 'Poi' )->count() );

    $poi = Doctrine::getTable( 'Poi' )->find(1); // Get the First POI
    $this->assertEquals( '+7 495 272 7934', $poi['phone']);
    $this->assertEquals( '+7 495 273 0409', $poi['phone2']);

    $poi = Doctrine::getTable( 'Poi' )->find(2); // Get the second POI
    $this->assertEquals( '+7 495 261 2211', $poi['phone']);
    $this->assertEquals( '+7 495 261 2200', $poi['phone2']);
  }
}
?>
