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

    private $vendor2;
    
    private $destination;

    private $specialChars = '&<>\'"';





    protected function setUp()
    {
      try {
        $pDB = Doctrine_Manager::connection(new PDO('sqlite::memory:'));
        Doctrine::createTablesFromModels( dirname(__FILE__).'/../../../../lib/model/doctrine');

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

        $this->destination = dirname( __FILE__ ) . '/../../export/poi/test.xml';
        $this->export = new XMLExportPOI( $this->vendor2, $this->destination );

      }
      catch(PDOException $e)
      {
        echo $e->getMessage();
      }
    }

    protected function tearDown()
    {
    }


    /**
     * getData() should only return data from Vendor passed in constructor
     */
    public function testGetDataFromCorrectVendor()
    {
      $data = $this->export->getData();
      $this->assertTrue( $data instanceof Doctrine_Collection );

      $this->assertEquals( 2, $data[0]->getVendorId() );
    }

    /**
     * test generateXML() has vendor-poi root tag with required attributes
     */
    public function testGenerateXMLHasVendorPoiWithRequiredAttribute()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );
      
      $this->assertTrue( $xmlElement instanceof SimpleXMLElement );

      //vendor-pois
      $this->assertEquals( $this->vendor2->getName(), (string) $xmlElement['vendor'] );
      $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $xmlElement['modified'] );

    }
    
    /**
     * test generateXML() has entry tags with required attributes
     */
    public function testGenerateXMLHasEntryTagsWithRequiredAttribute()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );

      //entry
      $this->assertEquals( 2, count( $xmlElement->entry ) );

      $prefix = 'vpid_';
      $this->assertStringStartsWith( $prefix, (string) $xmlElement->entry[0]['vpid'] );
      
      $vpid = (string) $xmlElement->entry[0]['vpid'];
      $this->assertGreaterThan( strlen( $prefix ), strlen( $vpid ) );

      $langAttribute = (string) $xmlElement->entry[0]['lang'];
      $this->assertEquals( 'en', $langAttribute );

      $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', (string) $xmlElement->entry[0]['modified'] );
    }

    /**
     * test generateXML() has geo-position tags with required attributes
     */
    public function testGenerateXMLHasGeoPositionTagsWithRequiredChildren()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );

      //geo-position

      //make sure we got not more than one node
      $this->assertEquals( 1, count( $xmlElement->xpath( '/vendor-pois/entry[1]/geo-position' ) ) );
      
      $longitude = (string) array_shift( $xmlElement->xpath( '/vendor-pois/entry[1]/geo-position/longitude' ) );
      $this->assertEquals( '0.1', $longitude );
      $latitude = (string) array_shift( $xmlElement->xpath( '/vendor-pois/entry[1]/geo-position/latitude' ) );
      $this->assertEquals( '0.2', $latitude );
    }

    /**
     * test generateXML() has a name tag
     */
    public function testGenerateXMLHasNameTag()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );

      $this->assertEquals( 1, count( $xmlElement->xpath( '/vendor-pois/entry[1]/name' ) ) );
      $name = (string) array_shift( $xmlElement->xpath( '/vendor-pois/entry[1]/name' ) );
      $this->assertEquals( 'test name', $name );
    }

     /**
     * test generateXML() has a category
     */
    public function testGenerateXMLHasCategoryTag()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );

      $this->assertGreaterThan( 0, count( $xmlElement->xpath( '/vendor-pois/entry[1]/category' ) ) );
      $name = (string) array_shift( $xmlElement->xpath( '/vendor-pois/entry[1]/category' ) );
    }

    /**
     * test generateXML() has address tags with required attributes
     */
    public function testGenerateXMLHasAddressTagsWithRequiredChildren()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );

      //make sure we got not more than one node
      $this->assertEquals( 1, count( $xmlElement->xpath( '/vendor-pois/entry[1]/address' ) ) );

      $address = array_shift( $xmlElement->xpath( '/vendor-pois/entry[1]/address' ) );

      $this->assertEquals( 'test street', (string) $address->street );
      $this->assertEquals( '12', (string) $address->houseno );
      $this->assertEquals( '1234', (string) $address->zip );
      $this->assertEquals( 'test town', (string) $address->city );
      $this->assertEquals( 'test district',(string)  $address->district );
      $this->assertEquals( 'test country', (string) $address->country );
    }

     /**
     * test if destination passed in constructor is stored
     */
    public function testHasDestination()
    {
      $destination = $this->export->getDestination();
      $this->assertType('string', $destination );
      $this->assertEquals( $this->destination, $this->export->getDestination() );
    }

    /**
     * @todo should test if file is writeable and throw exception if not
     * 
     * test if the put file can be read
     */
    public function testWriteXML()
    {
      $originalFileModTime = 0;
      
      if( file_exists( $this->destination ) )
        $originalFileModTime = filemtime( $this->destination );
      
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );
      $this->export->writeXMLToFile( $xmlElement );
      
      $this->assertTrue( file_exists( $this->destination ) );
      $this->assertNotEquals( $originalFileModTime, filemtime( $this->destination ) );
    }

    /**
     * make sure our special chars (&,<,>,',") are entityfied
     */
    public function testSpecialChars()
    {
      $data = $this->export->getData();
      $xmlElement = $this->export->generateXML( $data );
      $xml = $xmlElement->asXML();
      
      $escapedChars = htmlspecialchars( $this->specialChars );
      
      $this->assertRegExp( ':test name2' . $escaptedChars . ':', $xml );

      $address = array_shift( $xmlElement->xpath( '/vendor-pois/entry[2]/address' ) );
      $this->assertRegExp( ':test name2' . $escaptedChars . ':', $xml );
      $this->assertRegExp( ':test street2' . $escaptedChars . ':', $xml );
      $this->assertRegExp( ':13' . $escaptedChars . ':', $xml );
      $this->assertRegExp( ':test town2' . $escaptedChars . ':', $xml );
      $this->assertRegExp( ':test district2' . $escaptedChars . ':', $xml );
      $this->assertRegExp( ':test country2' . $escaptedChars . ':', $xml );

    }


}
?>
