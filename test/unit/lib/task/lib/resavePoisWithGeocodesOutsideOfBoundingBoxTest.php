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
		$this->geocode_look_up = '21 Mock Ave, Mockton, Mockia';
  }

  public function tearDown()
  {
    $this->vendor     = null;
    $this->geoEncoder = null;
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testPoisWithGeocodesOutsideOfBoundingBoxAreResaved()
  {
    $this->addVendorWithBoundaries('-10.0;-10.0;10.0;10.0');

    $mockGeocoder = $this->getMock( 'geoEncode', array( 'setAddress', 'getLongitude', 'getLatitude', 'getAccuracy' ) )
								 ;
    $mockGeocoder->expects( $this->exactly(2) )
								 ->method( 'setAddress' )
								 ->with(   $this->equalTo( $this->geocode_look_up ) )
								 ;
    $mockGeocoder->expects( $this->exactly(2) )
								 ->method( 'getLongitude' )
								 ->will(   $this->returnValue( 1.111111 ) )
								 ;
    $mockGeocoder->expects( $this->exactly(2) )
								 ->method( 'getLatitude' )
								 ->will(   $this->returnValue( 2.222222 ) )
								 ;
    $mockGeocoder->expects( $this->exactly(2) )
								 ->method( 'getAccuracy' )
								 ->will(   $this->returnValue( 9 ) )
								 ;
    //var_dump( $mockGeocoder->getLongitude() );

		$this->geoEncoder = $mockGeocoder;

    //These pois are out of bounds
    $badPoi1 = $this->addPoiWithLongLat( 12, 12 );
    $badPoi2 = $this->addPoiWithLongLat( 12, 12 );

    //These pois are withing bounds
    $this->addPoiWithLongLat(  1,  1 );
    $this->addPoiWithLongLat(  2,  2 );
    $this->addPoiWithLongLat( -1, -1 );

    $resaver = new resavePoisWithGeocodesOutsideOfBoundingBox();
    $resaver->run();

		$this->assertEquals( 1.111111, $badPoi1['longitude'] );
		$this->assertEquals( 2.222222, $badPoi1['latitude'] );

		$this->assertEquals( 1.111111, $badPoi2['longitude'] );
		$this->assertEquals( 2.222222, $badPoi2['latitude'] );
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
			'geocode_look_up' => $this->geocode_look_up,
    ) );

    $poi['Vendor'] = $this->vendor;
    $poi->setGeoEncoder( $this->geoEncoder );
    $poi->save();
		return $poi;
  }
}
