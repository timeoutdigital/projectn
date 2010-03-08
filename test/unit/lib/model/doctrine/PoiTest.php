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

    $this->object = ProjectN_Test_Unit_Factory::add( 'poi' );
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

    $this->assertEquals( 'test cat', $this->object[ 'VendorPoiCategories' ][ 0 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorPoiCategories' ][ 0 ][ 'vendor_id' ] );
    $this->assertEquals( 'test parent cat | test cat', $this->object[ 'VendorPoiCategories' ][ 1 ][ 'name' ] );
    $this->assertEquals( $vendor[ 'id' ], $this->object[ 'VendorPoiCategories' ][ 1 ][ 'vendor_id' ] );
  }

  public function testVendorCategoriesAreUnique()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->object->addVendorCategory( 'test cat', $vendor[ 'id' ] );
    $this->assertEquals( 1, $this->object[ 'VendorPoiCategories' ]->count() );
  }

  /**
   * Test the long/lat is either valid or null
   */
  public function testLongLat()
  {
      $vendorObj = Doctrine::getTable('Vendor')->findOneById( 1 );

      $poiObj = new Poi();
      $poiObj['poi_name']       = 'Fantastic';
      $poiObj['longitude']      = 0.0000;
      $poiObj['latitude']       = 0.0000;
      $poiObj['vendor_poi_id']  = 1111;
      $poiObj['Vendor']         = $vendorObj;
      $poiObj['street']         = "Tottenham Court Road";
      $poiObj['city']           = "London";
      $poiObj['country']        = "UK";

      $poiObj->setGeoEncodeLookUpString("Time out, Tottenham Court Road London");

      $poiObj->setGeoEncodeByPass(false);
      $poiObj->save();


      $this->assertTrue($poiObj['longitude'] != 0, "Test that there is no 0 in the longitude");


      $poiObj = new Poi();
      $poiObj['poi_name']       = 'Fantastic';
      $poiObj['longitude']      = 0.0000;
      $poiObj['latitude']       = 0.0000;
      $poiObj['vendor_poi_id']  = 1111;
      $poiObj['Vendor']         = $vendorObj;
      $poiObj['street']         = "  ";
      $poiObj['city']           = " ";
      $poiObj['country']        =  "  ";

      $poiObj->setGeoEncodeLookUpString(" ");

      $poiObj->setGeoEncodeByPass(false);
      $poiObj->save();

      $this->assertNull($poiObj['longitude'], "Test that a NULL is returned if the lookup has no values");

  }



}
?>
