<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Poi Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class PoiTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Poi
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->object = ProjectN_Test_Unit_Factory::get( 'poi' );
    $this->object[ 'VendorPoiCategory' ] = new Doctrine_Collection( Doctrine::getTable( 'Poi' ) );
    $this->object[ 'geoEncoder' ] = new MockGeoEncodeForPoiTest();
    $this->object->save();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * test if the add property adds properties successfuly
   */
  public function testAddProperty()
  {
    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('Poi')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test prop lookup', $this->object[ 'PoiProperty' ][ 0 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value', $this->object[ 'PoiProperty' ][ 0 ][ 'value' ] );

    $this->assertEquals( 'test prop lookup 2', $this->object[ 'PoiProperty' ][ 1 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value 2', $this->object[ 'PoiProperty' ][ 1 ][ 'value' ] );

    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();
    
    $poi = Doctrine::getTable('Poi')->findOneById( $this->object['id'] );

    $this->assertEquals(2, count($poi['PoiProperty']) );
  }

  /*
   * test if the add vendor category are added correctly
   */
  public function testAddVendorCategory()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->addVendorCategory( array( 'test parent cat', 'test cat' ), $vendor[ 'id' ] );
    $this->object->save();

    $this->object = Doctrine::getTable('Poi')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test cat', $this->object[ 'VendorPoiCategory' ][ 0 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorPoiCategory' ][ 0 ][ 'vendor_id' ] );
    $this->assertEquals( 'test parent cat | test cat', $this->object[ 'VendorPoiCategory' ][ 1 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorPoiCategory' ][ 1 ][ 'vendor_id' ] );
  }

  public function testVendorCategoriesAreUnique()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->assertEquals( 1, $this->object[ 'VendorPoiCategory' ]->count() );
  }

  /**
   * Test the long/lat is either valid or null
   */
  public function testLongLatIsFoundOrNull()
  {
      $poiObj = $this->createPoiWithLongitudeLatitude( 0.0, 0.0 );
      $poiObj['geocode_look_up'] = "Time out, Tottenham Court Road London";
      $poiObj['geoEncoder'] = new MockGeoEncodeForPoiTest();
      $poiObj->save();

      $this->assertTrue($poiObj['longitude'] != 0, "Test that there is no 0 in the longitude");


      $poiObj = $this->createPoiWithLongitudeLatitude( 0.0, 0.0 );
      $poiObj->setGeoEncodeLookUpString(" ");
      $poiObj['geoEncoder'] = new MockGeoEncodeForPoiTestWithoutAddress();
      $poiObj->save();

      $this->assertNull($poiObj['longitude'], "Test that a NULL is returned if the lookup has no values");
  }

  /**
   * longitude latitude needs to be truncated to fit the database (db was throwing errors)
   */
  public function testLongLatTruncatedToLengthDefinedInSchema()
  {
      $poi = $this->createPoiWithLongitudeLatitude( 180.123456789, 180.123456789 );
      $poi = $this->createPoiWithLongitudeLatitude( 180.123456789, 180.123456789 );
      $poi->save();

      $longitudeLength = (int) ProjectN_Test_Unit_Factory::getColumnDefinition( 'Poi', 'longitude', 'length' ) + 1;//+1 to account for decimal
      $this->assertEquals( $longitudeLength, strlen( $poi['longitude'] ) );

      $latitudeLength = (int) ProjectN_Test_Unit_Factory::getColumnDefinition( 'Poi', 'latitude', 'length' ) + 1;//+1 to account for decimal
      $this->assertEquals( $latitudeLength,  strlen( $poi['latitude'] ) );
  }

  private function createPoiWithLongitudeLatitude( $longitude, $latitude )
  {
      return ProjectN_Test_Unit_Factory::get( 'Poi', array(
        'longitude' => $longitude,
        'latitude'  => $latitude,
      ) );
  }

}

class MockGeoEncodeForPoiTest extends geoEncode
{
  private $address;

  public function setAddress( $address )
  {
    $this->address = $address;
  }
  public function numCallCount()
  {
    return $this->callCount;
  }
  public function getLongitude()
  {
    return 1.0;
  }
  public function getLatitude()
  {
    return 1.0;
  }
  public function getAccuracy()
  {
    return 9;
  }
}

class MockGeoEncodeForPoiTestWithoutAddress extends geoEncode
{
  public function setAddress( $address ) { }
  public function numCallCount() { }
  public function getLongitude() { }
  public function getLatitude() { }
  public function getAccuracy() { }
}
