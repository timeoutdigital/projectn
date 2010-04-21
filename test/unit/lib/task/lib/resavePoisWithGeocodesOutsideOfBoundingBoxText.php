<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for the eventPlaceIdsShouldExistInPoiXml
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class resavePoisWithGeocodesOutsideOfBoundingBoxTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @var geoEncode
   */
  private $geoEncoder;

  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  public function tearDown()
  {
    $this->vendor     = null;
    $this->geoEncoder = null;
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testPoisWithGeocodesOutsideOfBoundingBoxAreResaved()
  {
    $this->addVendorWithBoundaries('-10.0;10.0;10.0;-10.0');
    $this->geoEncoder = new MockGeoEncodeForBoundsTest();

    //These pois are out of bounds
    $this->addPoiWithLongLat( 12, 12 );
    $this->addPoiWithLongLat( 12, 12 );

    //These pois are withing bounds
    $this->addPoiWithLongLat(  1,  1 );
    $this->addPoiWithLongLat(  2,  2 );
    $this->addPoiWithLongLat( -1, -1 );

    $specification = new geocodeIsWithinBoundary();
    $this->assertEquals( 2, $specification->getFailingPois()->count() );
    $this->assertEquals( 3, $specification->getPassingPois()->count() );

    $resaver = new resavePoisWithGeocodesOutsideOfBoundingBox();
    $resaver->run();

    $this->assertEquals(2, $this->geoEncoder->numCallsToSetAddress() );
  }

  private function addVendorWithBoundaries( $bounds )
  {
    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'geo_boundries' => $bounds,
    ) );
  }

  private function addPoiWithLongLat( $long, $lat )
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi', array(
      'longitude' => $long,
      'latitude'  => $lat,
    ) );

    $poi['Vendor'] = $this->vendor;
    $poi->setGeoEncoder( $this->geoEncoder );
    $poi->save();
  }
}

class MockGeoEncodeForBoundsTest extends geoEncode
{
  private $callCount = 0;
  public function setAddress()
  {
    $this->callCount++;
  }
  public function numCallsToSetAddress()
  {
    return $this->callCount;
  }
}
