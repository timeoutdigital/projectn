<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../bootstrap.php';


/**
 * Test class geocodeIsWithinBoundary
 *
 * @package test
 * @subpackage specification.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class geocodeIsWithinBoundaryTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->vendor = $this->addVendorWithGeocodeBoundariesOf( 
      '-10.0;-10.0;10.0;10.0' );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetFailingPois()
  {
    //create pois that are out of bounding box
    $this->addPoiWithLongLatOf( -11,   1 ); //breaks left
    $this->addPoiWithLongLatOf(  11,   1 ); //breaks right
    $this->addPoiWithLongLatOf(   1,  11 ); //breaks top
    $this->addPoiWithLongLatOf(   1, -11 ); //breaks bottom

    //create a poi within bounding box
    $this->addPoiWithLongLatOf( 1, 1 );

    $specification = new geocodeIsWithinBoundary();
    $this->assertEquals( 4, $specification->getFailingPois()->count() );
  }

  public function testGetPassingPois()
  {
    //create pois that are out of bounding box
    $this->addPoiWithLongLatOf( -11,   1 ); //breaks left
    $this->addPoiWithLongLatOf(  11,   1 ); //breaks right
    $this->addPoiWithLongLatOf(   1,  11 ); //breaks top
    $this->addPoiWithLongLatOf(   1, -11 ); //breaks bottom

    //create a poi within bounding box
    $this->addPoiWithLongLatOf( 1, 1 );

    $specification = new geocodeIsWithinBoundary();
    $this->assertEquals( 1, $specification->getPassingPois()->count() );
  }

  private function addVendorWithGeocodeBoundariesOf( $boundaryValues )
  {
    return ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'geo_boundries' => $boundaryValues 
      ) );
  }

  private function addPoiWithLongLatOf( $long, $lat )
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi', array( 
      'longitude' => $long,
      'latitude' => $lat,
      ) );

    $poi['Vendor'] = $this->vendor;
    $poi->save();

    return $poi;
  }
}
