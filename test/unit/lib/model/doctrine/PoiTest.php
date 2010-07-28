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

  public function testStreetDoesNotContainPostCode()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $poi['street'] = '45 Some Street, SE1 9HG';

    $london = ProjectN_Test_Unit_Factory::get('Vendor', array( 'id' => 4 ));
    $poi['Vendor'] = $london;

    $poi->save();
    $this->assertEquals( $poi[ 'street' ], '45 Some Street' );
  }

  public function testApplyFeedGeoCodesIfValid()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

      $this->assertLessThan(1, $poi['PoiMeta']->count());

      $poi->applyFeedGeoCodesIfValid('1','-2.4975618975');

      $poi->save();

      $poi->applyFeedGeoCodesIfValid('1','-2.4975618975'); // Same LONG / LAT Should Meta Should not be added

      $this->assertEquals(1, $poi['PoiMeta']->count());

      $this->assertEquals('Geo_Source', $poi['PoiMeta'][0]['lookup']);

      $this->assertEquals('Feed', $poi['PoiMeta'][0]['value']);

      $poi->applyFeedGeoCodesIfValid('15.1789464','-2.4975618975');

      $poi->save();

      $this->assertEquals(2, $poi['PoiMeta']->count());

  }

  public function testPoiNameDoesNotEndWIthCommaAndOrSpace()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $poi['poi_name'] = 'foo,';
    $poi->save();
    $this->assertEquals( 'foo', $poi['poi_name'] );

    $poi['poi_name'] = 'bar ';
    $poi->save();
    $this->assertEquals( 'bar', $poi['poi_name'] );

    $poi['poi_name'] = 'baz, ';
    $poi->save();
    $this->assertEquals( 'baz', $poi['poi_name'] );

    $poi['poi_name'] = 'oof ,';
    $poi->save();
    $this->assertEquals( 'oof', $poi['poi_name'] );
  }

  /**
   * test if the street field is clean (doesnt have city or trailing jazz)
   */
  public function testCleanStreet()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
    $poi['street'] = 'Parque Mayer, Lisbon Street - Av Liberdade, Lisboa ';
    $poi->save();
    $this->assertNotEquals( " ", substr( $poi['street'], -1 ), "POI street cannot end in space" );
    $this->assertNotEquals( ",", substr( $poi['street'], -1 ), "POI street cannot end in comma" );
    $this->assertEquals( 0, preg_match( '/Lisboa\s$/', $poi['street'] ), 'POI street cannot end in city name' );
    $this->assertEquals( 1, preg_match( '/Lisbon/', $poi['street'] ), 'POI street can contain city name' );
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

      $poiObj['longitude'] = '151.207114';
      $poiObj['latitude'] = '-33.867139';

      $poiObj->save();

      $this->assertTrue( ( $poiObj['latitude'] == null ) && ( $poiObj['longitude'] == null ), 'Default longitude and latitude for Sydney is set to null' );

      $poiObj['longitude'] = '151.20711400';
      $poiObj['latitude'] = '-33.867138';
      $poiObj->save();

      $this->assertFalse( ( $poiObj['latitude'] == null ) && ( $poiObj['longitude'] == null ), 'Default longitude but not latitude for Sydney are preserved' );

      $poiObj['longitude'] = '151.20711200';
      $poiObj['latitude'] = '-33.867138';
      $poiObj->save();
     // sydney1: { long: '151.207114', lat: '-33.867139' }
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
  public function testFixHtmlEntities()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $poi['street'] = 'Parque Mayer - Av Liberdade, Lisboa ';
      $poi['poi_name'] = "My &quot;name&quot; is";
      $poi->save();

      $this->assertTrue( preg_match( '/&quot;/', $poi['poi_name'] ) == 0, 'POI name cannot contain HTML entities' );
      $this->assertEquals( $poi['poi_name'], 'My "name" is', 'POI name converts HTML entities to their appropriate characters' );

  }

  /**
   * Test the application of vendor-specific address transformations
   */
  public function testApplyAddressTransformations()
  {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "sydney", 'id' => 8 ) );
    $poi['street'] = 'Level 1, 8 Victoria Street';
    $poi['poi_name'] = "My &quot;name&quot; is";
    $transformations = sfConfig::get( 'app_vendor_address_transformations', array() );
    $poi->save();

    $savedPoiId = $poi->id;
    $poi->free( true ); unset( $poi );
    $poi = Doctrine::getTable( "Poi" )->findOneById( $savedPoiId );
    $poi['street'] = 'Level 1, 8 Victoria Street';
    $poi['poi_name'] = "My &quot;name&quot; is";

    $poi->save(); // Try Save Twice, make sure 'append' is not applied twice.

    $this->assertEquals( $poi[ 'additional_address_details' ], 'Level 1', 'Level <n> stripped from street and placed into additional_address_details' );
    $this->assertEquals( $poi[ 'house_no' ], 8, 'House number stripped from street and placed into house_no' );
    $this->assertEquals( $poi[ 'street' ], 'Victoria Street', 'Street left in street field' );
  }


   public function testAddMediaByUrlandSavePickLargerImage()
   {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $mediumImageUrl   = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h217/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $poi->addMediaByUrl( $smallImageUrl );
    $poi->addMediaByUrl( $largeImageUrl );
    $poi->addMediaByUrl( $mediumImageUrl );

    $poi->save();

    $savedPoiId = $poi->id;
    $poi->free( true ); unset( $poi );
    $poi = Doctrine::getTable( "Poi" )->findOneById( $savedPoiId );

    // after adding 3 images we expect to have only one image and it should be the large image
    $this->assertEquals( count( $poi[ 'PoiMedia' ]) ,1 , 'there should be only one PoiMedia attached to a Poi after saving' );
    $this->assertEquals( $poi[ 'PoiMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be attached to POI when adding more than one' );

   }

   /**
    * if there is an image attached to POI and a smaller one is being added, it should keep the larger image
    *
    */
   public function testAddMediaByUrlandSaveSkipSmallerImage()
   {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $poi->addMediaByUrl( $largeImageUrl );
    $poi->save();

    $savedPoiId = $poi->id;
    $poi->free( true ); unset( $poi );
    $poi = Doctrine::getTable( "Poi" )->findOneById( $savedPoiId );

    // adding a smaller size imahe
    $poi->addMediaByUrl( $smallImageUrl );
    $poi->save();

    $this->assertEquals( count( $poi[ 'PoiMedia' ]) ,1 , 'there should be only one PoiMedia attached to a Poi after saving' );
    $this->assertEquals( $poi[ 'PoiMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be kept adding a smaller sized one' );

   }

    /**
    * if there is an image attached to Poi and a larger one is being added, it should remove the existing image with the larger one
    *
    */
   public function testAddMediaByUrlandSaveRemoveSmallerImageAndSaveLargerOne()
   {
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $poi->addMediaByUrl( $smallImageUrl );
    $poi->save();

    $savedPoiId = $poi->id;
    $poi->free( true ); unset( $poi );
    $poi = Doctrine::getTable( "Poi" )->findOneById( $savedPoiId );

    // adding a smaller size imahe
    $poi->addMediaByUrl( $largeImageUrl );
    $poi->save();

    $this->assertEquals( count( $poi[ 'PoiMedia' ]) ,1 , 'there should be only one PoiMedia attached to a Poi after saving' );
    $this->assertEquals( $poi[ 'PoiMedia' ][0][ 'url' ], $largeImageUrl , 'larger should be saved' );

   }

   public function testValidateUrlAndEmail()
   {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Barcelona" ) );
      $poi['url'] = 'ccmatasiramis@bcn.cat'; //invalid url
      $poi['email'] = 'info@botafumeiro'; //invalid email

      $poi->save();
      $this->assertEquals( '', $poi['url'] , 'invalid url should be saved as NULL' );
      $this->assertEquals( '', $poi['email'] , 'invalid email should be saved as NULL' );
   }

  public function testStreetDoesNotEndWithCityName()
  {

    $streetNames = array(
        'foo, '                                                 => 'foo',
        'foo,'                                                  => 'foo',
        '152-154  King\'s Road, London'                         => '152-154  King\'s Road',
        '117 Commercial St Old Spitalfields Market , London'    => '117 Commercial St Old Spitalfields Market',
        '88 Marylebone Lane London'                             => '88 Marylebone Lane',
        'London'                                                => '',
        '211a Clapham Rd London'                                => '211a Clapham Rd',
        '5-7 Islington Studios London'                          => '5-7 Islington Studios',
        'Between London Bridge and Tower Bridge'                => 'Between London Bridge and Tower Bridge',
        '71-73 Torriano Av London'                              => '71-73 Torriano Av',
        '5 Bishopsgate Churchyard, London'                      => '5 Bishopsgate Churchyard',
        'Arch London, 50 Great Cumberland Place'                => 'Arch London, 50 Great Cumberland Place',
        'Southern Terrace, Westfield London, Ariel Way'         => 'Southern Terrace, Westfield London, Ariel Way',
        '5  Huguenot Place , 17a Heneage St , London '          => '5  Huguenot Place , 17a Heneage St',
        '5  Huguenot Place , 17a Heneage St,London '            => '5  Huguenot Place , 17a Heneage St',
        '5  Huguenot Place , 17a Heneage St,london'             => '5  Huguenot Place , 17a Heneage St'
    );

    $london = ProjectN_Test_Unit_Factory::get('Vendor', array( 'id' => 4 ));

    foreach ($streetNames as $initialStreetName => $expectedStreetName)
    {
         $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

         $poi['street'] = $initialStreetName;
         $poi[ 'city'] = 'London';
         $poi['Vendor'] = $london;
         $poi->save();

         $this->assertEquals( $expectedStreetName, $poi[ 'street' ] );

    }

  }

}

class MockGeoEncodeForPoiTest extends geoEncode
{
  private $address;

  public function _setAddress( $address )
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
  public function _setAddress( $address ) { }
  public function numCallCount() { }
  public function getLongitude() { }
  public function getLatitude() { }
  public function getAccuracy() { }
}

