<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Description of XMLExportPOITest
 *
 * @author ralph
 */
class XMLExportPOITest extends PHPUnit_Framework_TestCase
{
    /**
     * @var XMLEportPOI
     */
    private $export;

    /**
     * @var Vendor
     */
    private $vendor2;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $specialChars = '&<>\'"';

    /**
     * @var string
     */
    private $escapedSpecialChars;

    protected function setUp()
    {
      try {
        ProjectN_Test_Unit_Factory::createDatabases();
        ExportLogger::getInstance()->start();

        $vendor = new Vendor();
        $vendor['city'] = 'test';
        $vendor['language'] = 'en-GB';
        $vendor['time_zone'] = 'Europe/London';
        $vendor['inernational_dial_code'] = '+44';
        $vendor['airport_code'] = 'XXX';
        $vendor['country_code'] = 'XX';
        $vendor['country_code_long'] = 'XXX';
        $vendor['geo_boundries'] = '49.1061889648438;-8.623556137084959;60.8458099365234;1.75900018215179';
        $vendor->save();

        $this->vendor2 = new Vendor();
        $this->vendor2['city'] = 'test';
        $this->vendor2['language'] = 'en-GB';
        $this->vendor2['time_zone'] = 'Europe/London';
        $this->vendor2['inernational_dial_code'] = '+44';
        $this->vendor2['airport_code'] = 'XXX';
        $this->vendor2['country_code'] = 'XX';
        $this->vendor2['country_code_long'] = 'XXX';
        $this->vendor2['geo_boundries'] = '49.1061889648438;-8.623556137084959;60.8458099365234;1.75900018215179';
        $this->vendor2->save();

        $category = new PoiCategory();
        $category->setName( 'restaurant' );
        $category->save();

        ProjectN_Test_Unit_Factory::add( 'PoiCategory', array( 'name' => 'cinema' ) );

        ProjectN_Test_Unit_Factory::add( 'VendorPoiCategory', array( 'name' => 'french restaurant', 'vendor_id' => 2 ), false );
        ProjectN_Test_Unit_Factory::add( 'VendorPoiCategory', array( 'name' => 'italian restaurant', 'vendor_id' => 2 ), false );

        $uiCat = new UiCategory();
        $uiCat['name'] = "Something";
        $uiCat->link( "VendorPoiCategory", array( 1 ) );
        $uiCat->save();

        $uiCat = new UiCategory();
        $uiCat['name'] = "Something2";
        $uiCat->link( "VendorPoiCategory", array( 2 ) );
        $uiCat->save();


        $poi = new Poi();
        $poi->setPoiName( 'test name' );
        $poi->setStreet( 'test street' );
        $poi->setHouseNo('12' );
        $poi->setZips('1234' );
        $poi->setCity( 'test town' );
        $poi->setDistrict( 'test district' );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '-0.081888001' );
        $poi->setLatitude( 51.52454601 );
        $poi->setEmail( 'you@who.com' );
        $poi->setUrl( 'http://foo.com' );
        $poi->setPhone( '+44 208 123 1234' );
        $poi->setPhone2( '+44 208 223 2234' );
        $poi->setFax( '+44 208 323 3234' );
        $poi->setShortDescription( 'test short description' );
        $poi->setDescription( 'test description' );
        $poi->setPublicTransportLinks( 'test public transport' );
        //$poi->setPrice( 'test price' );
        $poi->setOpeningTimes( 'test opening times' );
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', array( 1, 2 ) );
        $poi->link('VendorPoiCategory', array( 1, 2 ) );
        $poi->save();

        $property = new PoiProperty();
        $property[ 'lookup' ] = 'poi key 1';
        $property[ 'value' ] = 'poi value 1';
        $poi['PoiProperty'][] = $property;
        $property->save();

        $property2 = new PoiProperty();
        $property2[ 'lookup' ] = 'poi key 2';
        $property2[ 'value' ] = 'poi value 2';
        $poi['PoiProperty'][] = $property2;
        $property2->save();

//        $property3 = new PoiProperty();
//        $property3[ 'lookup' ] = 'Critics_choice';
//        $property3[ 'value' ] = 'not y';
//        $property3->link( 'Poi', array( $poi['id'] ) );
//        $property3->save();

        $property4 = new PoiProperty();
        $property4[ 'lookup' ] = 'Critics_choice';
        $property4[ 'value' ] = 'Y';
        $poi['PoiProperty'][] = $property4;
        $property4->save();

        $media = new PoiMedia();
        $media[ 'ident' ] = 'md5 hash of the url';
        $media[ 'mime_type' ] = 'image/jpeg';
        $media[ 'url' ] = 'url';
        $media[ 'status' ] = 'valid';
        $media[ 'content_length' ] = 120;

        $poi['PoiMedia'][] = $media;

        $media->save();


        $poi = new Poi();
        $poi->setPoiName( 'test name2' . $this->specialChars );
        $poi->setStreet( 'test street2' . $this->specialChars );
        $poi->setHouseNo('13' . $this->specialChars );
        $poi->setZips('4321' );
        $poi->setCity( 'test town two' . $this->specialChars );
        $poi->setDistrict( 'test district2' . $this->specialChars );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '-0.081899' );
        $poi->setLatitude( '51.524500' );

        $poi->link('VendorPoiCategory', array( 1, 2 ) );
        $poi->link( 'Vendor', 2 );
        $poi->save();

        $property3 = new PoiProperty();
        $property3[ 'lookup' ] = 'poi key special' . $this->specialChars;
        $property3[ 'value' ] = 'poi value special' . $this->specialChars;
        $property3->link( 'Poi', array( $poi['id'] ) );
        $property3->save();

        // poi with empty street
        $poi = new Poi();
        $poi->setPoiName( 'test name3' . $this->specialChars );
        $poi->setStreet( ' ' );
        $poi->setHouseNo('13' . $this->specialChars );
        $poi->setZips('4321' );
        $poi->setCity( 'test town two' . $this->specialChars );
        $poi->setDistrict( 'test district2' . $this->specialChars );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '1234' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '-0.0814899' );
        $poi->setLatitude( '51.5245400' );

        $poi->link('VendorPoiCategory', array( 1, 2 ) );
        $poi->link( 'Vendor', 2 );
        $poi->save();

        //poi with not set street
        $poi = new Poi();
        $poi->setPoiName( 'test name4' . $this->specialChars );
        $poi->setHouseNo('13' . $this->specialChars );
        $poi->setZips('4321' );
        $poi->setCity( 'test town two' . $this->specialChars );
        $poi->setDistrict( 'test district2' . $this->specialChars );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '12345' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '-0.0814899' );
        $poi->setLatitude( '51.5245400' );

        $poi->link('VendorPoiCategory', array( 1, 2 ) );
        $poi->link( 'Vendor', 2 );
        $poi->save();

        // #687 Telephone Number Check
        $poi = new Poi();
        $poi->setPoiName( 'Invalid Telephone' );
        $poi->setStreet( 'test street' );
        $poi->setHouseNo('12' );
        $poi->setZips('1234' );
        $poi->setCity( 'test town' );
        $poi->setDistrict( 'test district' );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '-0.081888002' );
        $poi->setLatitude( 51.524543601 );
        $poi->setEmail( 'you@who.com' );
        $poi->setUrl( 'http://foo.com' );
        $poi->setPhone( '212 420 1934' );
        $poi->setPhone2( '12 769 1212' ); // Valid
        $poi->setFax( '444 1236' ); // Invalid no
        $poi->setShortDescription( 'test short description' );
        $poi->setDescription( 'test description' );
        $poi->setPublicTransportLinks( 'test public transport' );
        //$poi->setPrice( 'test price' );
        $poi->setOpeningTimes( 'test opening times' );
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', array( 1, 2 ) );
        $poi->link('VendorPoiCategory', array( 1, 2 ) );
        $poi->save();

        // #899 adding more poi to test exports
        $ui_eating = new UiCategory;
        $ui_eating['name'] = 'Eating & Drinking';
        $ui_eating->save();
        
        $ui_music =  new UiCategory;
        $ui_music['name'] = 'Music';
        $ui_music->save();

        $vendor = Doctrine::getTable( 'Vendor' )->find(1);

        // add vendor poi category and link it to eating and drinking ui category
        $vpc_restaurant = new VendorPoiCategory;
        $vpc_restaurant['name'] = 'Restaurant';
        $vpc_restaurant['vendor_id'] = $vendor['id'];
        $vpc_restaurant['UiCategory'][] = $ui_eating;
        $vpc_restaurant->save();

        // add one that link to other UI category.. not eating & drinking
        $vpc_music = new VendorPoiCategory;
        $vpc_music['name'] = 'Jazz';
        $vpc_music['vendor_id'] = $vendor['id'];
        $vpc_music['UiCategory'][] = $ui_music;
        $vpc_music->save();

        $latLongArray = explode(';', $vendor['geo_boundries']); // required as Unit factory generate random! which DOES repeat
        
        // POI without Description or short description
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array(
            'poi_name' => 'poi1',
            'description' => null,
            'short_description' => null,
            'latitude' => $latLongArray[0] + 0.1,
            'longitude' => $latLongArray[1] + 0.1,
        ) );
        $poi['VendorPoiCategory'] = new Doctrine_Collection( 'VendorPoiCategory' );
        $poi['VendorPoiCategory'][] = $vpc_restaurant;
        $poi->save();

        // POI with short description
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array(
            'poi_name' => 'poi2',
            'description' => null,
            'short_description' => 'short description',
            'latitude' => $latLongArray[0] + 0.2,
            'longitude' => $latLongArray[1] + 0.1,
        ) );
        $poi['VendorPoiCategory'] = new Doctrine_Collection( 'VendorPoiCategory' );
        $poi['VendorPoiCategory'][] = $vpc_restaurant;
        $poi->save();

        // POI with description
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array(
            'poi_name' => 'poi3',
            'description' => 'have description',
            'short_description' => null,
            'latitude' => $latLongArray[0] + 0.3,
            'longitude' => $latLongArray[1] + 0.1,

        ) );
        $poi['VendorPoiCategory'] = new Doctrine_Collection( 'VendorPoiCategory' );
        $poi['VendorPoiCategory'][] = $vpc_restaurant;
        $poi->save();

        // POI no description or description but link to non Eating and Drinking UI category
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array(
            'poi_name' => 'poi4',
            'description' => 'have description',
            'short_description' => null,
            'latitude' => $latLongArray[0] + 0.4,
            'longitude' => $latLongArray[1] + 0.1,

        ) );
        $poi['VendorPoiCategory'] = new Doctrine_Collection( 'VendorPoiCategory' );
        $poi['VendorPoiCategory'][] = $vpc_music;
        $poi->save();
        
        $this->runImportAndExport();
        
      }
      catch(PDOException $e)
      {
        echo $e->getMessage();
      }
    }

    protected function tearDown()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();

    }

    public function testDuplicateRecordsDontCountTowardsDuplicateLatLong()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $this->vendor2['geo_boundries'] = "1;1;2;2";
      $this->vendor2->save();

      $poi1 = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi1[ 'Vendor' ] = $this->vendor2;
      $poi1['latitude'] = 1.5;
      $poi1['longitude'] = 1.5;
      $poi1->save();

      $poi2 = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi2[ 'Vendor' ] = $this->vendor2;
      $poi2['latitude'] = 1.5;
      $poi2['longitude'] = 1.5;
      $poi2->save();

      $this->assertEquals( 2, Doctrine::getTable( 'Poi' )->count() );
      $this->runImportAndExport();
      $this->assertEquals( 0, count( $this->xml->xpath( '/vendor-pois/entry' ) ) );

      $poi2->setDuplicate( 'on' );
      $poi2->save();

      $this->assertEquals( 2, Doctrine::getTable( 'Poi' )->count() );
      $this->runImportAndExport();
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry' ) ) );
    }

    /**
    * Skip on lat/long out of bounds.
    */
    public function testPoiLatLongOutofVendorBounds()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $this->vendor2['geo_boundries'] = "1;1;2;2";
      $this->vendor2->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['latitude'] = 1.5;
      $poi['longitude'] = 1.5;
      $poi->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['latitude'] = 2.5;
      $poi['longitude'] = 1.5;
      $poi->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['latitude'] = 1.5;
      $poi['longitude'] = 2.5;
      $poi->save();

      $this->assertEquals( 3, Doctrine::getTable( 'Poi' )->count() );
      $this->runImportAndExport();

      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry' ) ) );
  }

    /**
   * Add UI Category to Export.
   */
    public function testAddUiCategory()
  {
      $this->domDocument = new DOMDocument();
      $this->domDocument->load( $this->destination );
      $this->xpath = new DOMXPath( $this->domDocument );

      $uiCategories = $this->xpath->query( "/vendor-pois/entry/version/content/property[@key='UI_CATEGORY']" );

      $this->assertEquals( 6, $uiCategories->length, "Should be exporting property 'UI_CATEGORY'." );
  }

    /**
   * Check to make sure we don't export a property named 'Critics_choice' with a value which is not 'y'.
   */
    public function testOnlyYesForCriticsChoiceProperty()
  {
      $this->domDocument = new DOMDocument();
      $this->domDocument->load( $this->destination );
      $this->xpath = new DOMXPath( $this->domDocument );

      //echo $this->domDocument->saveXML();

      $badCriticsChoice = $this->xpath->query( "/vendor-pois/entry/property[@key='Critics_choice' and lower-case(.) != 'y']" );
      $this->assertEquals( 0, $badCriticsChoice->length, "Should not be exporting property 'Critics_choice' with value not equal to 'y'" );
  }

    /**
     * @todo Someone didn't finish what they were doing.
     * Don't git blame me, I just removed the windows end-of-line chars! -pj
     */
    public function testPoisWithoutVendorCategoriesAreNotExported()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;

      $poi['latitude'] = 51.52454700;
      $poi['longitude'] = -0.081866800;
      $poi['VendorPoiCategory'] = new Doctrine_Collection( Doctrine::getTable( 'VendorPoiCategory' ) );
      $poi->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['poi_name'] = 'hello';
      $poi['latitude'] = 51.52453600;
      $poi['longitude'] = -0.08148800;
      $poi->addVendorCategory( "moose", $this->vendor2->id );
      $poi->save();

      $this->assertEquals( 2, Doctrine::getTable( 'Poi' )->count() );

      $this->runImportAndExport();
      $numEntries = $this->xml->xpath( '//entry' );

      $this->assertEquals( 1, count( $numEntries ) );
    }

    public function testCategoryTagsAreUnique()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $vendor['geo_boundries'] = "1;1;10;10";
      $vendor->save();

      $poi    = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $vendor;
      $poi->addVendorCategory( 'foo', $vendor );
      $poi->addVendorCategory( 'bar', $vendor );
      $poi['latitude'] = 5;
      $poi['longitude'] = 5;

      $poi->save();

      $poiCategory = ProjectN_Test_Unit_Factory::get( 'PoiCategory' );
      $poiCategory[ 'name' ] = 'cinema';
      $poiCategory->save();

      $vendorPoiCategory = Doctrine::getTable( 'VendorPoiCategory' )->findOneById( 1 );
      $vendorPoiCategory[ 'PoiCategory' ][] = $poiCategory;
      $vendorPoiCategory->save();

      $vendorPoiCategory = Doctrine::getTable( 'VendorPoiCategory' )->findOneById( 2 );
      $vendorPoiCategory[ 'PoiCategory' ][] = $poiCategory;
      $vendorPoiCategory->save();

      $this->destination = dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
      $this->export = new XMLExportPOI( $vendor, $this->destination );

      $this->export->run();
      sleep( 1 );
      $this->xml = simplexml_load_file( $this->destination );

      $this->assertEquals( 1, count( $this->xml->xpath( '//category' ) ) );
    }

    /**
     * test generated XML has vendor-poi root tag with required attributes
     */
    public function testGeneratedXMLHasVendorPoiWithRequiredAttribute()
    {
      $this->assertTrue( $this->xml instanceof SimpleXMLElement );

      //vendor-pois
      $this->assertEquals( XMLExport::VENDOR_NAME, (string) $this->xml['vendor'], 'Vendor should be "timeout"' );
      $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $this->xml['modified'] );
    }

    /**
     * test if poi with empty or no street is skipped
     */
    public function testIfPoiWithEmptyStreetIsSkipped()
    {
      //entry
      $this->assertEquals( 3, count( $this->xml->entry ) );
    }

    /**
     * test generated XML has entry tags with required attributes
     */
    public function testGeneratedXMLHasEntryTagsWithRequiredAttribute()
    {
      //entry
      $this->assertEquals( 3, count( $this->xml->entry ) );

      $prefix = 'XXX';
      $this->assertStringStartsWith( $prefix, (string) $this->xml->entry[0]['vpid'] );

      $vpid = (string) $this->xml->entry[0]['vpid'];
      $this->assertGreaterThan( strlen( $prefix ), strlen( $vpid ) );

      $langAttribute = (string) $this->xml->entry[0]['lang'];
      $this->assertEquals( 'en', $langAttribute );

      //$this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $this->xml->entry[0]['modified'] );
    }

    /**
     * test generated XML has geo-position tags with required attributes
     */
    public function testGenerateXMLHasGeoPositionTagsWithRequiredChildren()
    {
      //geo-position

      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/geo-position' ) ) );

      $xpathResult = $this->xml->xpath( '/vendor-pois/entry[1]/geo-position/longitude' );
      $longitude = (string) array_shift( $xpathResult );
      $this->assertEquals( '-0.081888001', $longitude );

      $xpathResult2 = $this->xml->xpath( '/vendor-pois/entry[1]/geo-position/latitude' );
      $latitude = (string) array_shift( $xpathResult2 );
      $this->assertEquals( '51.52454601', $latitude );
    }

    /**
     * test generateXML() has a name tag
     */
    public function testGenerateXMLHasNameTag()
    {
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/name' ) ) );

      $name = $this->xml->xpath( '/vendor-pois/entry[1]/name' );
      $name = (string) array_shift( $name );
      $this->assertEquals( 'test name', $name );
    }

     /**
     * test generateXML() has a category
     */
    public function testGenerateXMLHasCategoryTag()
    {
      $this->assertGreaterThan( 0, count( $this->xml->xpath( '/vendor-pois/entry[1]/category' ) ) );
    }

    /**
     * test generateXML() has address tags with required attributes
     */
    public function testGenerateXMLHasAddressTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/address' ) ) );

      $contact = $this->xml->xpath( '/vendor-pois/entry[1]/address' );
      $contact = array_shift( $contact );

      $this->assertEquals( 'test street', (string) $contact->street );
      $this->assertEquals( '12', (string) $contact->houseno );
      $this->assertEquals( '1234', (string) $contact->zip );
      $this->assertEquals( 'Test Town', (string) $contact->city );
      $this->assertEquals( 'test district',(string)  $contact->district );
      $this->assertEquals( 'GBR', (string) $contact->country );
    }

    /**
     * test generated XML has contact tags with required attributes
     */
    public function testGenerateXMLHasContactTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/contact' ) ) );

      $contact = $this->xml->xpath( '/vendor-pois/entry[1]/contact' );
      $contact = array_shift( $contact );

      $this->assertEquals( 'you@who.com', (string) $contact->email );
      $this->assertEquals( 'http://foo.com', (string) $contact->url );
      $this->assertEquals( '+44 208 123 1234', (string) $contact->phone, 'phone' );
      $this->assertEquals( '+44 208 223 2234', (string) $contact->phone2, 'phone 2' );
      $this->assertEquals( '+44 208 323 3234', (string) $contact->fax );
    }

    /**
     * test generated XML has content tags with required attributes
     *
     * @see the nodes with possible children (media, property, vendor-category) are tested separately
     */
    public function testGenerateXMLHasContentTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/version/content' ) ) );

      $content = $this->xml->xpath( '/vendor-pois/entry[1]/version/content' );
      $content = array_shift( $content );

      $this->assertEquals( 'test short description', (string) $content->{'short-description'} );
      $this->assertEquals( 'test description', (string) $content->description );
      $this->assertEquals( 'test public transport', (string) $content->{'public-transport'} );
      //$this->assertEquals( 'test price', (string) $content->price );
      $this->assertEquals( 'test opening times', (string) $content->{'openingtimes'} );
    }

    /**
     * make sure our special chars (&,<,>,',") are entityfied
     */
    public function testSpecialChars()
    {
      $xmlString = $this->xml->asXML();

      $this->assertRegExp( ':test name2' . $this->escapedSpecialChars . ':', $xmlString );

      $address = $this->xml->xpath( '/vendor-pois/entry[2]/address' );
      $address = array_shift( $address );


      $this->assertRegExp( ':test name2' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':test street2' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':13' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':test town two' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':test district2' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':poi key special' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':poi value special' . $this->escapedSpecialChars . ':', $xmlString );
    }

    /**
     * check vendor-category tags
     */
    public function testVendorCategoryTags()
    {
      $vendorPoiCategories = $this->xml->entry[0]->version->content->{'vendor-category'} ;
      $this->assertEquals( 'french restaurant', (string) $vendorPoiCategories[0] );
      $this->assertEquals( 'italian restaurant', (string) $vendorPoiCategories[1] );
    }

    /**
     * check properties tags
     */
    public function testPropertyTags()
    {
      $properties = $this->xml->entry[0]->version->content->property;
      $this->assertEquals( 'poi key 1', (string) $properties[0]['key'] );
      $this->assertEquals( 'poi value 1', (string) $properties[0] );
      $this->assertEquals( 'poi key 2', (string) $properties[1]['key'] );
      $this->assertEquals( 'poi value 2', (string) $properties[1] );
    }

    /**
     * Test Media Tag Exists
     */
    public function testMediaTags()
    {
      $properties = $this->xml->entry[0]->version->content->media;
      $this->assertEquals( 1, count( $properties ) );
    }

    public function testExportWithValidationOff()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;

      $poi['latitude'] = 11.52454700;
      $poi['longitude'] = 170.081866800;
      $poi['VendorPoiCategory'] = new Doctrine_Collection( Doctrine::getTable( 'VendorPoiCategory' ) );
      $poi->save();

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['poi_name'] = 'hello';
      $poi['latitude'] = 11.52453600;
      $poi['longitude'] = -0.08148800;
      $poi->addVendorCategory( "moo", $this->vendor2->id );
      $poi->save();

      $this->assertEquals( 2, Doctrine::getTable( 'Poi' )->count() );

      $this->destination = dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
      $this->export = new XMLExportPOI( $this->vendor2, $this->destination, false ); //validation is off!

      $this->export->run();
      $this->xml = simplexml_load_file( $this->destination );

      $numEntries = $this->xml->xpath( '//entry' );

      $this->assertEquals( 2, count( $numEntries ) );
    }

    public function testGetMediaUrlForProjectN()
    {
      $XMLExportDestination =  dirname( __FILE__ ) . '/../../export/poi/poitest.xml'  ;
      @unlink( $XMLExportDestination );

      // Destroy nd Re-create Database, as we needed a Clean database to test
      ProjectN_Test_Unit_Factory::destroyDatabases();      
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['poi_name'] = 'hello';
      $poi['latitude'] = 11.52453600;
      $poi['longitude'] = -0.08148800;
      $poi->addVendorCategory( "moo", $this->vendor->id );


      $media = new PoiMedia();
      $media[ 'ident' ] =   'e5f9ec048d1dbe19c70f720e002f9cb1';
      $media[ 'mime_type' ] = 'image/';
      $media[ 'url' ] =  'e5f9ec048d1dbe19c70f720e002f9cb1.jpg';
      $media[ 'status' ] = 'new';
      $poi['PoiMedia'][] = $media;

      $poi->save();

      //for default application
      $this->export = new XMLExportPOI( $this->vendor2, $XMLExportDestination, false ); //validation is off!

      $this->export->run();
      $this->xml = simplexml_load_file( $XMLExportDestination );

      $numEntries = $this->xml->xpath( '//entry' );

      //because the default application is frontend, All media URL's will be linked to AWS
      // we are expecting a url from AWS
      $this->assertEquals( 'http://projectn.s3.amazonaws.com/test/poi/media/e5f9ec048d1dbe19c70f720e002f9cb1.jpg' ,(string) $numEntries[0]->version->content->media  );

      //delete the export file and export it again with data_entry application
      @unlink( $XMLExportDestination );
    }

    public function testGetMediaUrlForDataEntry()
    {

      // Clean Database and create New Empty
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      // Set file Paths
      $XMLExportDestination =  dirname( __FILE__ ) . '/../../export/poi/poitest.xml'  ;
      $dummyImageFileName = sfConfig::get('sf_test_dir') .'/unit/import/uploads/media/poi/e5f9ec048d1dbe19c70f720e002f9cb1.jpg';
      
      // Mock Upload DIR
      $sf_upload_dir = sfConfig::get( 'sf_upload_dir' );
      sfConfig::set('sf_upload_dir', sfConfig::get('sf_test_dir') .'/unit/import/uploads' );

      // Mock App, Run as Data Entry branch
      $application = sfConfig::get( 'sf_app' ); //store the default application , should restore it back when we are done,
      sfConfig::set( 'sf_app' , 'data_entry' ); //now we are exporting in data_entry installation

      // Remove OLD Files
      @unlink( $XMLExportDestination );
      @unlink( $dummyImageFileName );  //delete the dummy image file if it exists

      // Run Import / Export
      $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor2;
      $poi['poi_name'] = 'hello';
      $poi['latitude'] = 11.52453600;
      $poi['longitude'] = -0.08148800;
      $poi->addVendorCategory( "moo", $this->vendor->id );

      $media = new PoiMedia();
      $media[ 'ident' ] =   'e5f9ec048d1dbe19c70f720e002f9cb1';
      $media[ 'mime_type' ] = 'image/';
      $media[ 'url' ] =  'e5f9ec048d1dbe19c70f720e002f9cb1.jpg';
      $media[ 'status' ] = 'new';
      $poi['PoiMedia'][] = $media;

      $poi->save();
      
      //create file in upload folder . mimic data_entry form uploaded it
      file_put_contents( $dummyImageFileName ,'' );
      //run data_entry export
      $this->export = new XMLExportPOI( $this->vendor2, $XMLExportDestination, false ); //validation is off!

      $this->export->run();
      $this->xml = simplexml_load_file( $XMLExportDestination );

      $numEntries = $this->xml->xpath( '//entry' );
      
      //now we are expecting a timeout/upload url
      $this->assertEquals( 'http://www.timeout.com/projectn/uploads/media/poi/e5f9ec048d1dbe19c70f720e002f9cb1.jpg' ,(string) $numEntries[0]->version->content->media  );

      // Reset config settings
      sfConfig::set( 'sf_app' , $application );  //put the application setting back, don't want to mess with the other tests
      sfConfig::set( 'sf_upload_dir' , $sf_upload_dir );  // put the upload_dir path back to default

    }
    
    private function runImportAndExport( $vendor = null)
    {
      // set default vendor as per previous test
      $vendor = ($vendor === null ) ? $this->vendor2 : $vendor;
        
      $this->destination = dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
      $this->export = new XMLExportPOI( $vendor, $this->destination );
      $this->export->setS3cmdClassName( 's3cmdTestMediaTags' );

      $this->export->run();
      $this->xml = simplexml_load_file( $this->destination );

      //ExportLogger::getInstance()->showErrors();
    }

    /**
     * Test the Nokia RegEx validation for phone, Phone 2 and Fax numbers when exporting.
     */
    public function testIsValidTelephoneNo()
    {
        $xml= simplexml_load_file( $this->destination );

        // Get the Last one to test for Invalid Number
        $contact = $xml->entry[2]->contact;

        $this->assertEquals( '+44 212 420 1934', (string) $contact->phone );
        $this->assertEquals( '+44 1 2769 1212', (string) $contact->phone2 );
        $this->assertEquals( '', (string) $contact->fax );
    }

    public function testCityIsCapitalised()
    {
      $contact = $this->xml->xpath( '/vendor-pois/entry[1]/address' );
      $contact = array_shift( $contact );
      $this->assertEquals( 'Test Town', (string) $contact->city );
    }

    public function testBeijingDistrictDoesNotEndWithStringDistrict()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      //we need beijing to be vendor 22
      //probably should've loaded the fixtures...
      for( $i=1; $i<22; $i++)
      {
        ProjectN_Test_Unit_Factory::add( 'Vendor' );
      }
      $beijing = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'Beijing' ) );

      //add some pois to beijing
      $poi1 = ProjectN_Test_Unit_Factory::get( 'Poi', array( 'district' => 'uppercase District' ) );
      $poi1[ 'Vendor' ] = $beijing;
      $poi1[ 'latitude' ] = 56.12;
      $poi1[ 'longitude' ] = -5.12;
      $poi1->save();

      $poi2 = ProjectN_Test_Unit_Factory::get( 'Poi', array( 'district' => 'lowercase district' ) );
      $poi2[ 'Vendor' ] = $beijing;
      $poi2[ 'latitude' ] = 56.13;
      $poi2[ 'longitude' ] = -5.13;
      $poi2->save();

      //export the beijing pois
      $this->vendor2 = $beijing; //see runImportAndExport for why this line is here
      $this->runImportAndExport();

      //should get back nodes in xml
      $entries = $this->xml->xpath( '/vendor-pois/entry' );
      $this->assertEquals( 2, count($entries) );

      //check uppercase 'District' is removed
      $contact = $this->xml->xpath( '/vendor-pois/entry[1]/address' );
      $contact = array_shift( $contact );
      $this->assertEquals( 'uppercase', (string) $contact->district );

      //check lowercase 'District' is removed
      $contact = $this->xml->xpath( '/vendor-pois/entry[2]/address' );
      $contact = array_shift( $contact );
      $this->assertEquals( 'lowercase', (string) $contact->district );
    }

    public function testNonBeijingDistrictCanEndWithStringDistrict()
    {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      //check non-beijing (id 22) vendors not affected
      $notBeijing = ProjectN_Test_Unit_Factory::add('Vendor');

      $poin1 = ProjectN_Test_Unit_Factory::get( 'Poi', array( 'district' => 'uppercase District' ) );
      $poin1[ 'Vendor' ] = $notBeijing;
      $poin1['latitude'] = 51;
      $poin1['longitude'] = -3;
      $poin1->save();

      $poin2 = ProjectN_Test_Unit_Factory::get( 'Poi', array( 'district' => 'lowercase district' ) );
      $poin2[ 'Vendor' ] = $notBeijing;
      $poin2['latitude'] = 51.1;
      $poin2['longitude'] = -3.1;
      $poin2->save();

      //export the non-beijing pois
      $this->vendor2 = $notBeijing; //see runImportAndExport for why this line is here
      $this->runImportAndExport();

      //should get back nodes in xml
      $entries = $this->xml->xpath( '/vendor-pois/entry' );
      $this->assertEquals( 2, count($entries) );

      //check uppercase 'District' is not removed
      $contact = $this->xml->xpath( '/vendor-pois/entry[1]/address' );
      $contact = array_shift( $contact );
      $this->assertEquals( 'uppercase District', (string) $contact->district );

      //check lowercase 'District' is not removed
      $contact = $this->xml->xpath( '/vendor-pois/entry[2]/address' );
      $contact = array_shift( $contact );
      $this->assertEquals( 'lowercase district', (string) $contact->district );
    }

    public function testAvoidExportingDuplicate()
    {
        $this->assertEquals( 5, Doctrine::getTable( 'Poi' )->findByVendorId(2)->count() );
        $this->assertEquals( 0, Doctrine::getTable( 'PoiReference' )->count() );
        
        $this->runImportAndExport();
        $entries = $this->xml->xpath( '/vendor-pois/entry' );
        $this->assertEquals(3, count($entries), "Should only be 3 Exported as 2 don't have street" );

        // add duplicate ship
        $poi1 = Doctrine::getTable( 'Poi' )->find(1);
        $poi5 = Doctrine::getTable( 'Poi' )->find(5);
        $poi5->setMasterPoi( $poi1 );
        $poi5->save();
        $this->assertEquals( 5, Doctrine::getTable( 'Poi' )->findByVendorId(2)->count() );
        $this->assertEquals( 1, Doctrine::getTable( 'PoiReference' )->count() );
        
        // Run export again and we only teo should be exported as 5th one marked as Duplicate of 1
        $this->runImportAndExport();
        $entries = $this->xml->xpath( '/vendor-pois/entry' );
        $this->assertEquals( 2, count($entries), "Should only be 2 as one marked as duplicate" );
        $this->assertNotEquals( $poi5['id'], (string)$entries[0]['vpid']);
        $this->assertNotEquals( $poi5['id'], (string)$entries[1]['vpid']);
        
    }

    public function testExportFilteringForEatingDrinkingWithNoDescription()
    {
        $this->runImportAndExport( Doctrine::getTable( 'vendor')->find(1) ); // Do export for vendor 1
        $xml= simplexml_load_file( $this->destination );

        $this->assertEquals( 9 , Doctrine::getTable( 'Poi')->findAll()->count() );
        $this->assertEquals( 3, count($xml), 'Only 3 POIs should be exported');
        
        // assert POIs exported
        $this->assertEquals( 'poi2', (string)$xml->entry[0]->name );
        $this->assertEquals( 'Eating & Drinking', (string)$xml->entry[0]->version->content->property[0] );

        $this->assertEquals( 'poi3', (string)$xml->entry[1]->name );
        $this->assertEquals( 'Eating & Drinking', (string)$xml->entry[1]->version->content->property[0] );
        
        $this->assertEquals( 'poi4', (string)$xml->entry[2]->name );
        $this->assertEquals( 'Music', (string)$xml->entry[2]->version->content->property[0] );
    }

    public function testExportHongkongGuisable()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData( 'data/fixtures' );

        $vendorHongKong = Doctrine::getTable( 'Vendor' )->findOneByCity( 'hong kong' );

        $poiHongkong = ProjectN_Test_Unit_Factory::get( 'poi', array( 'vendor_id'   => $vendorHongKong['id'],
                                                                      'poi_name' => 'IFC Mall',
                                                                      'district' => 'Central',
                                                                      'latitude' => '22.28529547',
                                                                      'longitude' => '114.15946873' ) );
        $poiHongkong->save();
        $poiShenzhen = ProjectN_Test_Unit_Factory::get( 'poi', array( 'vendor_id'   => $vendorHongKong['id'],
                                                                      'poi_name' => 'babyface',
                                                                      'district' => 'Shenzhen',
                                                                      'latitude' => '23.11138172',
                                                                      'longitude' => '113.26142599' ) );
        $poiShenzhen->save();
        $poiMacau = ProjectN_Test_Unit_Factory::get( 'poi', array(    'vendor_id'   => $vendorHongKong['id'],
                                                                      'poi_name' => 'IIUM, Auditorium',
                                                                      'district' => 'Macau',
                                                                      'latitude' => '22.18868336',
                                                                      'longitude' => '113.55275795' ) );
        $poiMacau->save();

        $XMLExportDestination =  dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
        $this->export = new XMLExportPOI( $vendorHongKong, $XMLExportDestination );
        $this->export->run();
        $this->xml = simplexml_load_file( $XMLExportDestination );
        $numEntries = $this->xml->xpath( '//entry' );

        $this->assertEquals( 3, count($numEntries) );

        //cleanup
        @unlink( $XMLExportDestination );
    }
}
/**
 * Purpose of this mockup class to Make "testMediaTags" test pass..
 * Since Image download task seperated and new status field added media tables,
 * testMediaTags will fail as it don't have any valid image or the image exist on amazon server ( hope this makes sense ;)
 */
class s3cmdTestMediaTags extends s3cmd
{
    // Override parent function and return a list of static images
    public function getListOfMediaAvailableOnAmazon( $vendorCity, $recordClass )
    {
        return array( 'md5 hash of the url.jpg' );
    }
}
?>
