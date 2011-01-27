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

    ProjectN_Test_Unit_Factory::createDatabases(  );

    $this->object = ProjectN_Test_Unit_Factory::get( 'poi' );
    $this->object[ 'VendorPoiCategory' ] = new Doctrine_Collection( Doctrine::getTable( 'Poi' ) );
    $this->object[ 'geocoderr' ] = new MockgeocoderForPoiTest();
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

  public function testGeoCodeLookupShouldNotBeBlank()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['geocode_look_up'] = "";
      $poi->save();
  }

  public function testMarkRecordAsDuplicate()
  {
    // Set False->False
    $this->assertFalse( $this->object->getDuplicate() );
    $this->assertEquals( 0, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );
    $this->object->setDuplicate( NULL );
    $this->object->save();
    $this->assertFalse( $this->object->getDuplicate() );
    $this->assertEquals( 0, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );

    // Set False->True
    $this->assertFalse( $this->object->getDuplicate() );
    $this->assertEquals( 0, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );
    $this->object->setDuplicate( 'on' );
    $this->object->save();
    $this->assertTrue( $this->object->getDuplicate() );
    $this->assertEquals( 1, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );

    // Set True->True
    $this->assertTrue( $this->object->getDuplicate() );
    $this->assertEquals( 1, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );
    $this->object->setDuplicate( 'on' );
    $this->object->save();
    $this->assertTrue( $this->object->getDuplicate() );
    $this->assertEquals( 1, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );

    // Set True->False
    $this->assertTrue( $this->object->getDuplicate() );
    $this->assertEquals( 1, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );
    $this->object->setDuplicate( NULL );
    $this->object->save();
    $this->assertFalse( $this->object->getDuplicate() );
    $this->assertEquals( 0, Doctrine::getTable( 'PoiMeta' )->findByLookupAndRecordId( 'Duplicate', $this->object['id'] )->count() );
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

  public function testApplyFeedGeoCodesIfValidEmptyValues()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['latitude'] = null;
      $poi['longitude'] = null;

      $poi->applyFeedGeoCodesIfValid( '', '');
      $this->assertEquals( null, $poi['latitude']);
      $this->assertEquals( null, $poi['longitude']);

      $poi->applyFeedGeoCodesIfValid( '0', '0');
      $this->assertEquals( null, $poi['latitude']);
      $this->assertEquals( null, $poi['longitude']);
  }

  public function testApplyFeedGeoCodesIfValidException()
  {
      $vendor = Doctrine::getTable( 'Vendor' )->find(1);
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

      // get lat long
      $latLongSet = explode( ';', $vendor['geo_boundries'] );

      $this->setExpectedException( 'PoiException' );
      // Apply a Geocode that is outside vendor boundaries, it should throw exception
      $poi->applyFeedGeoCodesIfValid( ($latLongSet[2] + 1 ),'-2.4975618975'); // add 1 to high latitude, makes it out of boundary
      
  }

  public function testApplyFeedGeoCodesIfValidValidGeoCode()
  {
      $vendor = Doctrine::getTable( 'Vendor' )->find(1);
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

      // get lat long
      $latLongSet = explode( ';', $vendor['geo_boundries'] );

      // Apply a Geocode that is outside vendor boundaries, it should throw exception
      $poi->applyFeedGeoCodesIfValid( ($latLongSet[0] + 0.5 ), ($latLongSet[1] + 0.5) ) ; // Use the minimum lat/long

  }

  public function testApplyFeedGeoCodesIfValid()
  {
      $vendor = Doctrine::getTable( 'Vendor' )->find(1);
      $latLongSet = explode( ';', $vendor['geo_boundries'] );

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );

      $this->assertLessThan(1, $poi['PoiMeta']->count());

      $poi->applyFeedGeoCodesIfValid( $latLongSet[0], $latLongSet[1]);

      $poi->save();

      $poi->applyFeedGeoCodesIfValid($latLongSet[0], $latLongSet[1]); // Same LONG / LAT Should Meta Should not be added

      $this->assertEquals(1, $poi['PoiMeta']->count());

      $this->assertEquals('Geo_Source', $poi['PoiMeta'][0]['lookup']);

      $this->assertEquals('Feed', $poi['PoiMeta'][0]['value']);

      $poi->applyFeedGeoCodesIfValid($latLongSet[0] + 0.5, $latLongSet[1]);

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
   * Check to see if addVendorCategory add's Empty array value array('')
   */
  public function testAddVendorCategoryEmpty()
  {
      $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );

      // Add String Category
      $this->object->addVendorCategory( 'empty 1', $vendor[ 'id' ] );

      // Empty string
      $this->object->addVendorCategory( '', $vendor[ 'id' ] );

      // array
      $this->object->addVendorCategory( array('empty2 ', 'empty 3'), $vendor[ 'id' ] );

      // array plus empty
      $this->object->addVendorCategory( array( 'empty 4', '', ' ', ' empty 5' ), $vendor[ 'id' ] );

      $this->object->save(); // Save to DB

      // validate
      $categoryTable = Doctrine::getTable( 'VendorPoiCategory' )->findAll();

      // Bootstrap adding a default 'test name' category as First
      $this->assertEquals('empty 1' , $categoryTable[1]['name']);
      $this->assertEquals('empty2  | empty 3' , $categoryTable[2]['name']);
      $this->assertEquals('empty 4 |  empty 5' , $categoryTable[3]['name']);

      // Object Exception!
      try{

          $this->object->addVendorCategory($categoryTable, $vendor[ 'id' ] );
          $this->assertEquals(false, true, 'Error: addVendorCategory should throw an exception when an object passed as parameter');

      }catch(Exception $exception)
      {
          $this->assertEquals(false, false); // Exception captured

      }

      // @todo: addVendorCategory do not removes whitespaces in parameter
      // 21-07-10: live database found few duplicate category for same vendor id!
      $this->markTestIncomplete();

  }

  /**
   * Test the long/lat is either valid or null
   */
  public function testLongLatIsFoundOrNull()
  {
      $poiObj = $this->createPoiWithLongitudeLatitude( 0.0, 0.0 );
      $poiObj['geocode_look_up'] = "Time out, Tottenham Court Road London";
      $poiObj['geocoderr'] = new MockgeocoderForPoiTestReturnNulllatLong();
      $poiObj->save();

      $this->assertTrue($poiObj['longitude'] === null , "Test that there is no 0 in the longitude");
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
   * test if setting the name of a Poi ensures HTML entities are decoded and Trimmed
   */
  public function testCleanStringFields()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $poi['street'] = 'Parque Mayer - Av Liberdade, Lisboa ';
      $poi['poi_name'] = "My &quot;name&quot; is";
      $poi->save();

      $this->assertTrue( preg_match( '/&quot;/', $poi['poi_name'] ) == 0, 'POI name cannot contain HTML entities' );
      $this->assertEquals( $poi['poi_name'], 'My "name" is', 'POI name converts HTML entities to their appropriate characters' );

      // test for Trim refs #525
      $poiTrim = ProjectN_Test_Unit_Factory::get( 'Poi' );

      $poiTrim['poi_name'] = PHP_EOL . 'spaced poi name      ';
      $poiTrim['street'] = '45 Some Street, SE1 9HG      '; // Postcode should be removed alongwith whitespace and tab space

      $london = ProjectN_Test_Unit_Factory::get('Vendor', array( 'id' => 4 ));

      $poiTrim['Vendor'] = $london;

      $poiTrim->save();

      $this->assertEquals( 'spaced poi name', $poiTrim[ 'poi_name' ] );
      $this->assertEquals( '45 Some Street', $poiTrim[ 'street' ], 'Expected Street Name: 45 Some Street, SE1 9HG' );

      // make sure leading and trailing commas get removed
      $poiTrim['poi_name'] = ',Poi name is ,';

      // save
      $poiTrim->save();

      // assert
      $this->assertEquals('Poi name is', $poiTrim['poi_name'], 'trim failed to remove leading and/or trailing comma(s)');
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

   /**
   * Test Media Class -> PopulateByUrl with Redirecting Image URLS
   */
  public function testMediaPopulateByUrlForRedirectingLink()
  {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi->addMediaByUrl( 'http://www.timeout.com/img/44494/image.jpg' ); // url Redirect to another...
      $poi->addMediaByUrl( 'http://www.timeout.com/img/44484/image.jpg' ); // another url Redirect to another...
      $poi->save();

      $this->assertEquals(2, $poi['PoiMedia']->count(), 'addMediaByUrl() will add All images to DB and another one will be chosen on export');
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

  public function testFixPhoneNumbers()
  {
      $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor' );
      $vendor['inernational_dial_code'] = '+3493';
      $vendor->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['phone'] = ' + &amp; *12 34 5 6 7 8  !"  & $ ';
      $poi['phone2'] = ' + &amp; *12 34 5 6 7 8  !"  & $ ';
      $poi['fax'] = ' + &amp; *12 34 5 6 7 8  !"  & $ ';
      $poi[ 'Vendor' ] = $vendor;
      $poi->save();

      $this->assertEquals( '+3493 1 234 5678', $poi['phone'], "there seems something wrong with the phone number cleaning for field phone" );
      $this->assertEquals( '+3493 1 234 5678', $poi['phone2'], "there seems something wrong with the phone number cleaning for field phone2" );
      $this->assertEquals( '+3493 1 234 5678', $poi['fax'], "there seems something wrong with the phone number cleaning for field fax" );
  }

   public function testFormatPhoneWhenPhoneHasAlreadyPrefixedWithInternationalDialCode()
   {

      $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor' );
      $vendor['inernational_dial_code'] = '+3493';
      $vendor->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi['phone'] = '+3493 9 3424 6577';
      $poi[ 'Vendor' ] = $vendor;
      $poi->save();

      $this->assertEquals( '+3493 9 3424 6577', $poi['phone'], "formatPhone should'nt change the phone number if it's already prefixed with the dial code" );
   }

   public function testAddVendorCategoryHTMLDecode()
   {
    $vendorCategory = "Neighborhood &amp; pubs";
    //$vendorCategory = "Neighborhood pubs | Pick-up joints";
    $vendor = Doctrine::getTable('Vendor')->findOneById( 1 );
    $this->object->addVendorCategory( $vendorCategory, $vendor[ 'id' ] );
    $this->object->save();

    $this->assertEquals( 'Neighborhood & pubs', $this->object[ 'VendorPoiCategory' ][0]['name'] );
   }

   public function testDuplicatePois()
   {
       $masterPoi1 = Doctrine::getTable( 'Poi' )->find(1);
       $masterPoi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );
       $duplicatePoi1 = ProjectN_Test_Unit_Factory::add( 'Poi' );
       $duplicatePoi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );

       $this->assertEquals( 4, Doctrine::getTable( 'Poi' )->count() );
       $this->assertEquals( 0, Doctrine::getTable( 'PoiReference' )->count() );

       // Add duplicate POI to Master 1
       $masterPoi1['DuplicatePois'][] = $duplicatePoi1;
       $masterPoi1['DuplicatePois'][] = $duplicatePoi2;
       $masterPoi1->save();

       $masterPoi1->refresh( true );
       $masterPoi2->refresh( true );
       $duplicatePoi1->refresh( true );
       $duplicatePoi2->refresh( true );

       $this->assertEquals( 2, Doctrine::getTable( 'PoiReference' )->count() );
       $this->assertEquals( 2, $masterPoi1['DuplicatePois']->count() );
       $this->assertEquals( 0, $masterPoi2['DuplicatePois']->count() );
       $this->assertEquals( 1, $duplicatePoi1['MasterPoi']->count() );
       $this->assertEquals( 1, $duplicatePoi2['MasterPoi']->count() );
   }

   public function testDuplicatePoisUniqueDuplicatePoi()
   {
       $masterPoi1 = Doctrine::getTable( 'Poi' )->find(1);
       $masterPoi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );
       $duplicatePoi1 = ProjectN_Test_Unit_Factory::add( 'Poi' );

       $this->assertEquals( 3, Doctrine::getTable( 'Poi' )->count() );
       $this->assertEquals( 0, Doctrine::getTable( 'PoiReference' )->count() );

       // Add Duplicate 1 to Master 1
       $masterPoi1['DuplicatePois'][] = $duplicatePoi1;
       $masterPoi1->save();
       $this->assertEquals( 1, Doctrine::getTable( 'PoiReference' )->count() );

       $this->setExpectedException( 'Exception' );
       $masterPoi2['DuplicatePois'][] = $duplicatePoi1;
       $masterPoi2->save();

   }

   public function testIsDuplicate()
   {
       $masterPoi = Doctrine::getTable( 'Poi' )->find(1);
       $duplicatePoi = ProjectN_Test_Unit_Factory::add( 'Poi' );
       $masterPoi['DuplicatePois'][] = $duplicatePoi;
       $masterPoi->save();

       $this->assertEquals( 2, Doctrine::getTable( 'Poi' )->count() );
       $this->assertEquals( 1, Doctrine::getTable( 'PoiReference' )->count() );

       $this->assertTrue( $duplicatePoi->isDuplicate() );
       $this->assertFalse( $duplicatePoi->isMaster() );
       
       $this->assertFalse( $masterPoi->isDuplicate() );
       $this->assertTrue( $masterPoi->isMaster() );
       
   }

   public function testPreSaveThrowExceptionWhenDiffVendorPoiReferenced()
   {
       $masterPoiVendor1 = Doctrine::getTable( 'Poi' )->find(1);
       $duplicatePoiVendor1 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 1 ) );
       $duplicatePoiVendor2 = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 2 ) );
       $masterPoiVendor1['DuplicatePois'][] = $duplicatePoiVendor1;
       $masterPoiVendor1['DuplicatePois'][] = $duplicatePoiVendor2;
       $this->assertEquals( 2, $masterPoiVendor1['DuplicatePois']->count() );
       $this->setExpectedException( 'Exception' );
       $masterPoiVendor1->save();

   }
   public function testCleanStringNullifyEmptyReviewDateValidDate()
   {
       $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
       $poi['review_date'] = '2010-11-11';
       $poi->save();

       $this->assertEquals( '2010-11-11' , $poi['review_date']);
   }

   public function testCleanStringNullifyEmptyReviewDateInvalidDate()
   {
       $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
       $poi['review_date'] = ' ';
       $poi->save();

       $this->assertEquals( null , $poi['review_date']);
   }

   /**
    * Doctrine version < 1.2.3 have bug with self referencing table This test is to proof that Doctrine upgrade should have fixed it
    * this will fail in Older version of Doctrine and pass from V1.2.3
    */
   public function testDoctrineBugSelfReference()
   {

       ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'poi 1') );
       ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'poi 2') );
       ProjectN_Test_Unit_Factory::add( 'Poi', array( 'poi_name' => 'poi 3') );

       $poi1 = Doctrine::getTable( 'Poi' )->findOneByPoiName( 'poi 1' );
       $poi2 = Doctrine::getTable( 'Poi' )->findOneByPoiName( 'poi 2' );
       $poi3 = Doctrine::getTable( 'Poi' )->findOneByPoiName( 'poi 3' );

       // Link POI 1 -> POI 2
       $poi1['DuplicatePois'][0] = $poi2;
       $poi1->save();

       $poi1->refresh();
       $poi1->refreshRelated();

       // proof that POI 1 is related to POI 2
       $this->assertEquals( 1, $poi1['DuplicatePois']->count() );
       $this->assertEquals( $poi2['id'], $poi1['DuplicatePois'][0]['id'] );

       // Link POI1 -> POI3, but also link POI1 -> POI2 again to simulate FORM processing
       $poi1['DuplicatePois'][0] = $poi2;
       $poi1['DuplicatePois'][1] = $poi3;
       $poi1->save();

       // Refresh to get Latest changes pulled from database (like reference updates)
       $poi1->refresh();
       $poi1->refreshRelated();
       $poi2->refresh();
       $poi2->refreshRelated();
       $poi3->refresh();
       $poi3->refreshRelated();

       // proof that each POI 2 and 3 duplicate of POI 1 and POI 1 is the master poi of Poi 2 & 3
       $this->assertEquals( 2, $poi1['DuplicatePois']->count() );
       $this->assertEquals( $poi2['id'], $poi1['DuplicatePois'][0]['id'] );
       $this->assertEquals( $poi3['id'], $poi1['DuplicatePois'][1]['id'] );
       // Reverse lookup
       $this->assertEquals( $poi1['id'], $poi2['MasterPoi'][0]['id'] );
       $this->assertEquals( $poi1['id'], $poi3['MasterPoi'][0]['id'] );

       // Assert reference table for Records
       $referenceTable = Doctrine::getTable( 'PoiReference' )->findAll( Doctrine_Core::HYDRATE_ARRAY );
       $this->assertEquals( 2, count($referenceTable) );
       $this->assertEquals( $poi1['id'], $referenceTable[0]['master_poi_id']);
       $this->assertEquals( $poi1['id'], $referenceTable[1]['master_poi_id']);
       $this->assertEquals( $poi2['id'], $referenceTable[0]['duplicate_poi_id']);
       $this->assertEquals( $poi3['id'], $referenceTable[1]['duplicate_poi_id']);
   }
}

class MockgeocoderForPoiTest extends geocoder
{
  private $address;

  public function responseIsValid()
  {
      return true;
  }
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

  public function getLookupUrl()
  {
      return 'mockgeocoder for poi lookup url';
  }

  protected function apiKeyIsValid( $apiKey ) { }

  protected function processResponse( $response ) { }
}

class MockgeocoderForPoiTestWithoutAddress extends geocoder
{
  public function _setAddress( $address ) { }
  public function numCallCount() { }
  public function getLongitude() { }
  public function getLatitude() { }
  public function getAccuracy() { }

  public function getLookupUrl()
  {
      return 'mockgeocoder for poi lookup url';
  }

  public function responseIsValid()
  {
      return true;
  }

  protected function apiKeyIsValid( $apiKey ) { }
  protected function processResponse( $response ) { }
}

class MockgeocoderForPoiTestReturnNulllatLong extends MockgeocoderForPoiTest
{
    public function getLongitude()
    {
        return null;
    }

    public function getLatitude()
    {
        return null;
    }
}