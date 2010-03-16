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

        $vendor = new Vendor();
        $vendor['city'] = 'test';
        $vendor['language'] = 'en-GB';
        $vendor['time_zone'] = 'Europe/London';
        $vendor['inernational_dial_code'] = '+44';
        $vendor['airport_code'] = 'XXX';
        $vendor->save();

        $this->vendor2 = new Vendor();
        $this->vendor2['city'] = 'test';
        $this->vendor2['language'] = 'en-GB';
        $this->vendor2['time_zone'] = 'Europe/London';
        $this->vendor2['inernational_dial_code'] = '+44';
        $this->vendor2['airport_code'] = 'XXX';
        $this->vendor2->save();

        $category = new PoiCategory();
        $category->setName( 'restaurant' );
        $category->save();

        ProjectN_Test_Unit_Factory::add( 'PoiCategory', array( 'name' => 'cinema' ) );

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
        $poi->setLongitude( '0.1' );
        $poi->setLatitude( '0.2' );
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
        $poi->save();

        $property = new PoiProperty();
        $property[ 'lookup' ] = 'poi key 1';
        $property[ 'value' ] = 'poi value 1';
        $property->link( 'Poi', array( $poi['id'] ) );
        $property->save();

        $property2 = new PoiProperty();
        $property2[ 'lookup' ] = 'poi key 2';
        $property2[ 'value' ] = 'poi value 2';
        $property2->link( 'Poi', array( $poi['id'] ) );
        $property2->save();

        $property = new PoiMedia();
        $property[ 'ident' ] = 'md5 hash of the url';
        $property[ 'mime_type' ] = 'image/';
        $property[ 'url' ] = 'url';
        $property->link( 'Poi', array( $poi['id'] ) );
        $property->save();

        $poi = new Poi();
        $poi->setPoiName( 'test name2' . $this->specialChars );
        $poi->setStreet( 'test street2' . $this->specialChars );
        $poi->setHouseNo('13' . $this->specialChars );
        $poi->setZips('4321' );
        $poi->setCity( 'test town2' . $this->specialChars );
        $poi->setDistrict( 'test district2' . $this->specialChars );
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '0.3' );
        $poi->setLatitude( '0.4' );
        $poi->link( 'Vendor', 2 );
        $poi->save();

        $property3 = new PoiProperty();
        $property3[ 'lookup' ] = 'poi key special' . $this->specialChars;
        $property3[ 'value' ] = 'poi value special' . $this->specialChars;
        $property3->link( 'Poi', array( $poi['id'] ) );
        $property3->save();

        $this->destination = dirname( __FILE__ ) . '/../../export/poi/poitest.xml';
        $this->export = new XMLExportPOI( $this->vendor2, $this->destination );

        $this->export->run();
        $this->xml = simplexml_load_file( $this->destination );

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
      $this->assertEquals( '0.1', $longitude );
      
      $xpathResult2 = $this->xml->xpath( '/vendor-pois/entry[1]/geo-position/latitude' );
      $latitude = (string) array_shift( $xpathResult2 );
      $this->assertEquals( '0.2', $latitude );
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
      $this->assertRegExp( ':test town2' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':test district2' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':poi key special' . $this->escapedSpecialChars . ':', $xmlString );
      $this->assertRegExp( ':poi value special' . $this->escapedSpecialChars . ':', $xmlString );
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
     * check properties tags
     */
    public function testMediaTags()
    {
      $this->markTestSkipped( 'temporarily removed media' );
      $properties = $this->xml->entry[0]->version->content->media;
      $this->assertEquals( 'image/', (string) $properties[0]['mime-type'] );
      $this->assertEquals( 'url', (string) $properties[0] );
    }
}
?>
