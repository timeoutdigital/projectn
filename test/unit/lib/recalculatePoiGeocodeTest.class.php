<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the curl importer
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class recalculatePoiGeocodeTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testAddPois()
  {
    $recalc = new recalculatePoiGeocode();
    $pois = new Doctrine_Collection( Doctrine::getTable( 'Event' ) );

    $this->setExpectedException( 'Exception' );
    $recalc->addPois( $pois );
  }

  public function testRun()
  {
    $poi1 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 
      'longitude' => 1.234, 
      'latitude'  => 5.678 ) );

    $poi2 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 
      'longitude' => 100.00, 
      'latitude'  => 1.234 ) );

    $poi3 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 
      'longitude' => 1.234, 
      'latitude'  => 5.678 ) );

    $pois = new Doctrine_Collection( Doctrine::getTable( 'Poi' ) );
    $pois[] = $poi1;
    $pois[] = $poi2;
    $pois[] = $poi3;

    $mockGeoEncode = new mockGeoencode();
    $mockGeoEncode->setLongitude( 111.11 );
    $mockGeoEncode->setLatitude( 222.22 );

    $recalc = new recalculatePoiGeocode();
    $recalc->setGeoEncoder( $mockGeoEncode );
    $recalc->addPois( $pois );
    $recalc->run();

    $this->assertEquals( $poi1['longitude'], 111.11 );
    $this->assertEquals( $poi1['latitude'],  222.22 );

    $this->assertEquals( $poi2['longitude'], 111.11 );
    $this->assertEquals( $poi2['latitude'],  222.22 );

    $this->assertEquals( $poi3['longitude'], 111.11 );
    $this->assertEquals( $poi3['latitude'],  222.22 );
  }
}

class mockGeoEncode extends geoEncode
{
  private $latitude;
  private $longitude;

  public function setLongitude( $value )
  {
    $this->longitude = $value;
  }

  public function setLatitude( $value )
  {
    $this->latitude = $value;
  }

  public function getLongitude()
  {
    return $this->longitude;
  }

  public function getLatitude()
  {
    return $this->latitude;
  }

  public function setAddress(){}
  public function getGeoCode(){}
}
