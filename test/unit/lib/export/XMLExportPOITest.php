<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../lib/export/XMLExport.class.php';
require_once dirname(__FILE__) . '/../../../../lib/export/XMLExportPOI.class.php';
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
     *
     * @var string
     */
    private $destination;

    /**
     *
     * @var string
     */
    private $specialChars = '&<>\'"';
    
    protected function setUp()
    {
      try {
        ProjectN_Test_Unit_Factory::createSqliteMemoryDb();

        $vendor = new Vendor();
        $vendor->setCity('test');
        $vendor->setLanguage('english');
        $vendor->save();

        $this->vendor2 = new Vendor();
        $this->vendor2->setCity('test');
        $this->vendor2->setLanguage('german');
        $this->vendor2->save();

        $category = new PoiCategory();
        $category->setName( 'test' );
        $category->save();

        ProjectN_Test_Unit_Factory::add( 'PoiCategory', array( 'name' => 'test 2' ) );

        $poi = new Poi();
        $poi->setPoiName( 'test name' );
        $poi->setStreet( 'test street' );
        $poi->setHouseNo('12' );
        $poi->setZips('1234' );
        $poi->setCity( 'test town' );
        $poi->setDistrict( 'test district' );
        $poi->setCountry( 'test country' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setCountryCode( 'uk' );
        $poi->setLongitude( '0.1' );
        $poi->setLatitude( '0.2' );
        $poi->setEmail( 'you@who.com' );
        $poi->setUrl( 'http://foo.com' );
        $poi->setPhone( '+44 0208 123 1234' );
        $poi->setPhone2( '+44 0208 223 2234' );
        $poi->setFax( '+44 0208 323 3234' );
        $poi->setShortDescription( 'test short description' );
        $poi->setDescription( 'test description' );
        $poi->setPublicTransportLinks( 'test public transport' );
        //$poi->setPrice( 'test price' );
        $poi->setOpeningTimes( 'test opening times' );

        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', array( 1, 2 ) );

        $poi->save();
        
        $poi = new Poi();
        $poi->setPoiName( 'test name2' . $this->specialChars );
        $poi->setStreet( 'test street2' . $this->specialChars );
        $poi->setHouseNo('13' . $this->specialChars );
        $poi->setZips('4321' );
        $poi->setCity( 'test town2' . $this->specialChars );
        $poi->setDistrict( 'test district2' . $this->specialChars );
        $poi->setCountry( 'test country2' . $this->specialChars );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setCountryCode( 'uk' );
        $poi->setLongitude( '0.3' );
        $poi->setLatitude( '0.4' );
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', 1);

        $poi->save();

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
      ProjectN_Test_Unit_Factory::destroySqliteMemoryDb();
    }

    /**
     * test generated XML has vendor-poi root tag with required attributes
     */
    public function testGeneratedXMLHasVendorPoiWithRequiredAttribute()
    {
      $this->assertTrue( $this->xml instanceof SimpleXMLElement );

      //vendor-pois
      $this->assertEquals( $this->vendor2->getName(), (string) $this->xml['vendor'] );
      $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $this->xml['modified'] );
    }
    
    /**
     * test generated XML has entry tags with required attributes
     */
    public function testGeneratedXMLHasEntryTagsWithRequiredAttribute()
    {
      //entry
      $this->assertEquals( 2, count( $this->xml->entry ) );

      $prefix = 'vpid_';
      $this->assertStringStartsWith( $prefix, (string) $this->xml->entry[0]['vpid'] );

      $vpid = (string) $this->xml->entry[0]['vpid'];
      $this->assertGreaterThan( strlen( $prefix ), strlen( $vpid ) );

      $langAttribute = (string) $this->xml->entry[0]['lang'];
      $this->assertEquals( 'en', $langAttribute );

      $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $this->xml->entry[0]['modified'] );
    }

    /**
     * test generated XML has geo-position tags with required attributes
     */
    public function testGenerateXMLHasGeoPositionTagsWithRequiredChildren()
    {
      //geo-position

      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/geo-position' ) ) );

      $longitude = (string) array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/geo-position/longitude' ) );
      $this->assertEquals( '0.1', $longitude );
      $latitude = (string) array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/geo-position/latitude' ) );
      $this->assertEquals( '0.2', $latitude );
    }

    /**
     * test generateXML() has a name tag
     */
    public function testGenerateXMLHasNameTag()
    {
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/name' ) ) );
      $name = (string) array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/name' ) );
      $this->assertEquals( 'test name', $name );
    }

     /**
     * test generateXML() has a category
     */
    public function testGenerateXMLHasCategoryTag()
    {
      $this->assertGreaterThan( 0, count( $this->xml->xpath( '/vendor-pois/entry[1]/category' ) ) );
      $name = (string) array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/category' ) );
    }

    /**
     * test generateXML() has address tags with required attributes
     */
    public function testGenerateXMLHasAddressTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/address' ) ) );

      $contact = array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/address' ) );

      $this->assertEquals( 'test street', (string) $contact->street );
      $this->assertEquals( '12', (string) $contact->houseno );
      $this->assertEquals( '1234', (string) $contact->zip );
      $this->assertEquals( 'test town', (string) $contact->city );
      $this->assertEquals( 'test district',(string)  $contact->district );
      $this->assertEquals( 'test country', (string) $contact->country );
    }

    /**
     * test generated XML has contact tags with required attributes
     */
    public function testGenerateXMLHasContactTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/contact' ) ) );

      $contact = array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/contact' ) );

      $this->assertEquals( 'you@who.com', (string) $contact->email );
      $this->assertEquals( 'http://foo.com', (string) $contact->url );
      $this->assertEquals( '+44 0208 123 1234', (string) $contact->phone );
      $this->assertEquals( '+44 0208 223 2234', (string) $contact->phone2 );
      $this->assertEquals( '+44 0208 323 3234', (string) $contact->fax );
    }

    /**
     * test generated XML has content tags with required attributes
     */
    public function testGenerateXMLHasContentTagsWithRequiredChildren()
    {
      //make sure we got not more than one node
      $this->assertEquals( 1, count( $this->xml->xpath( '/vendor-pois/entry[1]/content' ) ) );

      $content = array_shift( $this->xml->xpath( '/vendor-pois/entry[1]/content' ) );

      $this->assertEquals( 'test short description', (string) $content->{'short-description'} );
      $this->assertEquals( 'test description', (string) $content->description );
      $this->assertEquals( 'test public transport', (string) $content->{'public-transport'} );
      //$this->assertEquals( 'test price', (string) $content->price );
      $this->assertEquals( 'test opening times', (string) $content->{'opening-times'} );
    }

    /**
     * make sure our special chars (&,<,>,',") are entityfied
     */
    public function testSpecialChars()
    {
      $escapedChars = htmlspecialchars( $this->specialChars );
      $xmlString = $this->xml->asXML();

      $this->assertRegExp( ':test name2' . $escaptedChars . ':', $xmlString );

      $address = array_shift( $this->xml->xpath( '/vendor-pois/entry[2]/address' ) );
      $this->assertRegExp( ':test name2' . $escaptedChars . ':', $xmlString );
      $this->assertRegExp( ':test street2' . $escaptedChars . ':', $xmlString );
      $this->assertRegExp( ':13' . $escaptedChars . ':', $xmlString );
      $this->assertRegExp( ':test town2' . $escaptedChars . ':', $xmlString );
      $this->assertRegExp( ':test district2' . $escaptedChars . ':', $xmlString );
      $this->assertRegExp( ':test country2' . $escaptedChars . ':', $xmlString );
    }


}
?>