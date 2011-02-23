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

  public function testIsDuplicate()
  {
      $this->assertEquals( 0, Doctrine::getTable('Poi')->count() );
      $this->assertEquals( 0, Doctrine::getTable('PoiReference')->count() );

      $masterPoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi->setMasterPoi( $masterPoi );
      $duplicatePoi->save();

      $this->assertEquals( 2, Doctrine::getTable('Poi')->count() );
      $this->assertEquals( 1, Doctrine::getTable('PoiReference')->count() );

      $this->assertTrue( Doctrine::getTable('Poi')->isDuplicate( $duplicatePoi['id'] ) );
      $this->assertFalse( Doctrine::getTable('Poi')->isDuplicate( $masterPoi['id'] ) );
  }

  public function testGetMasterOf()
  {
      $this->assertEquals( 0, Doctrine::getTable('Poi')->count() );
      $this->assertEquals( 0, Doctrine::getTable('PoiReference')->count() );

      $masterPoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi->setMasterPoi($masterPoi );
      $duplicatePoi->save();

      $poi = Doctrine::getTable( 'Poi' )->getMasterOf( $duplicatePoi['id'] );
      $this->assertEquals( $masterPoi['id'], $poi['id']);
      $this->assertFalse( Doctrine::getTable( 'Poi' )->getMasterOf( $masterPoi['id'] ) );
  }

  public function testGetDuplicatesOf()
  {
      $this->assertEquals( 0, Doctrine::getTable('Poi')->count() );
      $this->assertEquals( 0, Doctrine::getTable('PoiReference')->count() );

      $masterPoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi->setMasterPoi($masterPoi);
      $duplicatePoi->save();
      $duplicatePoi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $duplicatePoi2->setMasterPoi($masterPoi );
      $duplicatePoi2->save();

      $poi = Doctrine::getTable( 'Poi' )->getDuplicatesOf( $masterPoi['id'], Doctrine_Core::HYDRATE_ARRAY );
      $this->assertTrue( is_array( $poi ) );
      $this->assertEquals( 2, count( $poi  ) );
      $this->assertEquals( $duplicatePoi['id'], $poi[0]['id']);
      $this->assertEquals( $duplicatePoi2['id'], $poi[1]['id']);
  }

  public function testSearchAllNonDuplicateAndNonMasterPoisBy()
  {
      $masterPoi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'test name 1' ) );
      $duplicatePoi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'test name 2'  ) );
      $normalPoiVendor1 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'test name 3' ) );
      $normalPoiVendor2 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 2, 'poi_name' => 'test name 4' ) );
      $this->assertEquals( 4, Doctrine::getTable( 'Poi' )->count() );
      $this->assertEquals( 0, Doctrine::getTable( 'PoiReference' )->count() );

      // add Duplicate
      $duplicatePoi->setMasterPoi($masterPoi);
      $duplicatePoi->save();
      $this->assertEquals( 1, Doctrine::getTable( 'PoiReference' )->count() );

      // Get as Array
      $pois = Doctrine::getTable( 'Poi' )->searchAllNonDuplicateAndNonMasterPoisBy( 1, 't', Doctrine_Core::HYDRATE_ARRAY );
      $this->assertTrue( is_array( $pois ) );
      $this->assertEquals( 1, count($pois) );
      $this->assertEquals( $normalPoiVendor1['id'], $pois[0]['id'] );

      // Test the other vendor
      $pois = Doctrine::getTable( 'Poi' )->searchAllNonDuplicateAndNonMasterPoisBy( 2, 't', Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals( 1, count($pois) );
      $this->assertEquals( $normalPoiVendor2['id'], $pois[0]['id'] );
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

  public function testAddWherePoiIsNotDuplicate_FindAllDuplicateLatLongsAndApplyWhitelist()
  {      
      $poi1 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi3 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi4 = ProjectN_Test_Unit_Factory::add( 'Poi' );

      $this->assertEquals( 4, Doctrine::getTable( 'Poi' )->count() );
      $pois = Doctrine::getTable( 'Poi' )->findAllDuplicateLatLongsAndApplyWhitelist( 1 );
      $this->assertEquals( 0, count($pois), 'There is no duplicate yet! it should bring nothing back' );

      // add duplicate geocode
      // 1 == 4
      $poi1['latitude'] = $poi4['latitude'];
      $poi1['longitude'] = $poi4['longitude'];
      $poi1->save();
      $pois = Doctrine::getTable( 'Poi' )->findAllDuplicateLatLongsAndApplyWhitelist( 1 );
      $this->assertEquals( 1, count($pois), 'Should have 1 Duplicate' );

      // add the ID4 as Duplicate of Poi 1
      $poi4->setMasterPoi($poi1);
      $poi4->save();
      $pois = Doctrine::getTable( 'Poi' )->findAllDuplicateLatLongsAndApplyWhitelist( 1 );
      $this->assertEquals( 0, count($pois), 'Should bring nothing back, as 4th one identified as duplicate of POI 1' );
  }

  public function testAddWherePoiIsNotDuplicate_FindAllValidByVendorId()
  {
      $poi1 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi3 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $poi4 = ProjectN_Test_Unit_Factory::add( 'Poi' );
      $this->assertEquals( 4, Doctrine::getTable( 'Poi' )->count() );
      
      $validPois = Doctrine::getTable( 'Poi' )->findAllValidByVendorId( 1 );
      $this->assertEquals( 4, $validPois->count(), 'There is no Duplicate Yet, it should bring back 4 POIs' );

      // Mark poi 3 as Poi 1's Duplicate
      $poi3->setMasterPoi($poi1);
      $poi3->save();
      
      $validPois = Doctrine::getTable( 'Poi' )->findAllValidByVendorId( 1 );
      $this->assertEquals( 3, $validPois->count(), 'Only 3, One marked as duplicate POI' );
      $this->assertNotEquals( $poi3['id'], $validPois[0]['id'], 'POI ID3 should be excluded in the LIST' );
      $this->assertNotEquals( $poi3['id'], $validPois[1]['id'], 'POI ID3 should be excluded in the LIST' );
      $this->assertNotEquals( $poi3['id'], $validPois[2]['id'], 'POI ID3 should be excluded in the LIST' );
  }

}
?>
