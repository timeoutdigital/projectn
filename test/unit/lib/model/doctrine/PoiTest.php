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

  public function testPoiNameDoesNotEndWIthCommaAndOrSpace()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $poi['poi_name'] = 'foo,';
    $this->assertEquals( 'foo', $poi['poi_name'] );

    $poi['poi_name'] = 'bar ';
    $this->assertEquals( 'bar', $poi['poi_name'] );

    $poi['poi_name'] = 'baz, ';
    $this->assertEquals( 'baz', $poi['poi_name'] );

    $poi['poi_name'] = 'oof ,';
    $this->assertEquals( 'oof', $poi['poi_name'] );
  }

  /**
   * test if the street field is clean (doesnt have city or trailing jazz)
   */
  public function testCleanStreet()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $poi['street'] = 'Parque Mayer - Av Liberdade, Lisboa ';
      $poi->save();
      $this->assertNotEquals( " ", substr( $poi['street'], -1 ), "POI street cannot end in space" );
      $this->assertNotEquals( ",", substr( $poi['street'], -1 ), "POI street cannot end in comma" );
      $this->assertEquals( false, strpos( $poi['street'], $poi['Vendor']['city'] ), "POI street cannot contain vendor city name" );
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

  public function testAddVendorCategoryDoesNotDeleteExistingVendorCategories()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi->save();

    $poi->refresh();
    $this->assertEquals(1, count($poi['VendorPoiCategory'])); //bootstrap adds a category already

    $poi->addVendorCategory( 'Foo', $poi['Vendor']['id'] );
    $poi->save();
    $this->assertEquals(2, count($poi['VendorPoiCategory'])); //bootstrap adds a category already
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
   * Test the long/lat is either valid or null
   */
  public function testDefaultLongLatIsSetToNull()
  {
      $poiObj = $this->createPoiWithLongitudeLatitude( 0.0, 0.0 );
      $poiObj['geocode_look_up'] = "Time out, Tottenham Court Road London";
      $poiObj['geoEncoder'] = new MockGeoEncodeForPoiTest();
      $poiObj->save();

      $poiObj['longitude'] = '151.20711400';
      $poiObj['latitude'] = '-33.86713900';

      $poiObj->save();

      $this->assertTrue( ( $poiObj['latitude'] == null ) && ( $poiObj['longitude'] == null ), 'Default longitude and latitude for Sydney is set to null' );

      $poiObj['longitude'] = '151.20711400';
      $poiObj['latitude'] = '-33.867138';
      $poiObj->save();

      $this->assertFalse( ( $poiObj['latitude'] == null ) && ( $poiObj['longitude'] == null ), 'Default longitude but not latitude for Sydney are preserved' );
      
      $poiObj['longitude'] = '151.20711200';
      $poiObj['latitude'] = '-33.867138';
      $poiObj->save();
      
      $this->assertFalse( ( $poiObj['latitude'] == null ) && ( $poiObj['longitude'] == null ), 'Non default longitude and latitude for Sydney are preserved' );
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

  /**
   * 
   * test the  getter and setter functions for the Critics_choice flag
   */
  public function testSetterGetterCriticsChoiceFlag()
  {
    $this->object['CriticsChoiceProperty'] = true;
    $this->assertEquals( 'Y', $this->object['CriticsChoiceProperty'] );

    //see todo in subject class
    //$this->object['CriticsChoiceProperty'] = false;
    //$this->assertNull( $this->object['CriticsChoiceProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setCriticsChoiceProperty( 'not a boolean' );
    $this->assertNull( $this->object->getCriticsChoiceProperty() );
  }

  /**
   *
   * test the  getter and setter functions for the Recommended flag
   */
  public function testSetterGetterRecommendedFlag()
  {
    $this->object['RecommendedProperty'] = true;
    $this->assertEquals( 'Y', $this->object['RecommendedProperty'] );

    //$this->object['RecommendedProperty'] = false;
    //$this->assertNull( $this->object['RecommendedProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setRecommendedProperty('not a boolean');
    $this->assertNull( $this->object->getRecommendedProperty() );
  }

  /**
   *
   * test the  getter and setter functions for the Free flag
   */
  public function testSetterGetterFreeFlag()
  {
    $this->object['FreeProperty'] = true;
    $this->assertEquals( 'Y', $this->object['FreeProperty'] );

    //$this->object['RecommendedProperty'] = false;
    //$this->assertNull( $this->object['RecommendedProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setFreeProperty('not a boolean');
    $this->assertNull( $this->object->getFreeProperty() );
  }

  public function testAddTimeoutUrl()
  {
    $this->object['TimeoutLinkProperty'] = '';
    $this->assertNull( $this->object['TimeoutLinkProperty'] );

    $url = 'http://www.timeout.com/london/event/123';
    $this->object['TimeoutLinkProperty'] = $url;
    $this->assertEquals( $url, $this->object['TimeoutLinkProperty'] );
  }

  private function createPoiWithLongitudeLatitude( $longitude, $latitude )
  {
      return ProjectN_Test_Unit_Factory::get( 'Poi', array(
        'longitude' => $longitude,
        'latitude'  => $latitude,
      ) );
  }

   /**
   * test if setting the name of a Poi ensures HTML entities are decoded
   */
  public function testSetPoiName()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $poi['street'] = 'Parque Mayer - Av Liberdade, Lisboa ';
      $poi['poi_name'] = "My &quot;name&quot; is";
      $poi->save();

      $this->assertTrue( preg_match( '/&quot;/', $poi['poi_name'] ) == 0, 'POI name cannot contain HTML entities' );
      $this->assertEquals( $poi['poi_name'], 'My "name" is', 'POI name converts HTML entities to their appropriate characters' );

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
