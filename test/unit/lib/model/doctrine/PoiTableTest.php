<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Poi Table Model
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
class PoiTableTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var PoiTable
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->object = Doctrine::getTable('Poi');
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
   * test getVendorUidFieldName() returns the right string
   */
  public function testGetVendorUidFieldName()
  {
    $column = $this->object->getVendorUidFieldName();
    $this->assertTrue( $this->object->hasColumn($column) );
  }

  public function testFindAllValidByVendor()
  {
     $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

     $numPoisWithLongLat = 2;
     for( $i=0; $i < $numPoisWithLongLat; $i++ )
     {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'longitude' ] = null;
      $poi[ 'latitude' ]  = null;
      $poi->setgeocoderByPass( true );
      $poi->save();
     }

     $numPoisThatAreGood = 3;
     for( $i=0; $i < $numPoisThatAreGood; $i++ )
     {
       $poi    = ProjectN_Test_Unit_Factory::add( 'Poi' );
     }

     $pois = Doctrine::getTable( 'Poi' )->findAllValidByVendorId( $vendor['id'] );
     $this->assertEquals( 3, $pois->count() );
  }

  public function testFindByVendorPoiIdAndVendor()
  {
    $chicago   = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'chicago',   'language' => 'en-US' ) );
    $singapore = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'singapore', 'language' => 'en-US' ) );
    $lisbon    = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'lisbon',    'language' => 'pt' ) );

    $this->addPoi( '1234', 'I am in Chicago',   $chicago );
    $this->addPoi( '1234', 'I am in Singapore', $singapore );
    $this->addPoi( '1234', 'I am in Lisbon',    $lisbon );

    $poiTable = Doctrine::getTable( 'Poi' );

    $chicagoPoi = $poiTable->findOneByVendorPoiIdAndVendorId( '1234', $chicago['id'] );
    $this->assertEquals( 'I am in Chicago', $chicagoPoi['poi_name'] );

    $singaporePoi = $poiTable->findOneByVendorPoiIdAndVendorId( '1234', $singapore['id'] );
    $this->assertEquals( 'I am in Singapore', $singaporePoi['poi_name'] );

    $lisbonPoi = $poiTable->findOneByVendorPoiIdAndVendorId( '1234', $lisbon['id'] );
    $this->assertEquals( 'I am in Lisbon', $lisbonPoi['poi_name'] );
  }

  public function testFindByIdAndVendorLanguage()
  {
    $vendorData = array(
      array( 'city' => 'lisbon', 'language' => 'pt' ),
      array( 'city' => 'moscow', 'language' => 'ru' ),
      array( 'city' => 'london', 'language' => 'en' ),
    );

    $vendors = array();
    foreach( $vendorData as $data )
      $vendors[] = ProjectN_Test_Unit_Factory::add( 'Vendor', $data );

    $this->assertEquals( 3, Doctrine::getTable( 'Vendor' )->count() );

    foreach( $vendors as $vendor )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $vendor;
      $poi['vendor_poi_id'] = '1234';
      $poi->save();
      $this->assertEquals( 1, $vendor['Poi']->count() );
    }

    $poi = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( '1234', 'ru' );
    $this->assertEquals( 'moscow', $poi['Vendor']['city'] );
  }

  private function addPoi( $vendorPoiId, $name, $vendor )
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi[ 'poi_name' ]      = $name;
    $poi[ 'vendor_poi_id' ] = $vendorPoiId;
    $poi[ 'Vendor' ]        = $vendor;
    $poi->save();
    return $poi;
  }

}
?>
