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
        $media[ 'mime_type' ] = 'image/';
        $media[ 'url' ] = 'url';
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
      $this->assertEquals( 4, $uiCategories->length, "Should be exporting property 'UI_CATEGORY'." );
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
      $poi->addVendorCategory( "moo", $this->vendor2->id );
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

      $poi    = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $vendor;
      $poi->addVendorCategory( 'foo', $vendor );
      $poi->addVendorCategory( 'bar', $vendor );
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
     * test generated XML has entry tags with required attributes
     */
    public function testGeneratedXMLHasEntryTagsWithRequiredAttribute()
    {
      //entry
      $this->assertEquals( 2, count( $this->xml->entry ) );

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
      $this->assertEquals( 'test town', (string) $contact->city );
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
      //  print_r( Doctrine::getTable('PoiMedia')->findAll()->toArray() );
      //echo $this->xml->entry[0]->asXML();
      $properties = $this->xml->entry[0]->version->content->media;
      $this->assertEquals( 'image/', (string) $properties[0]['mime-type'] );
      $this->assertEquals( 'http://projectn.s3.amazonaws.com/test/poi/media/md5 hash of the url.jpg', (string) $properties[0] );
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
      $this->export = new XMLExportPOI( $this->vendor2, $this->destination, false );

      $this->export->run();
      $this->xml = simplexml_load_file( $this->destination );

      $numEntries = $this->xml->xpath( '//entry' );

      $this->assertEquals( 2, count( $numEntries ) );
    }

    private function runImportAndExport()
    {
      $this->destination = dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
      $this->export = new XMLExportPOI( $this->vendor2, $this->destination );

      $this->export->run();
      $this->xml = simplexml_load_file( $this->destination );

      //ExportLogger::getInstance()->showErrors();
    }
}
?>
