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

        $poi = new Poi();
        $poi->setPoiName( 'test name' );
        $poi->setStreet( 'test street' );
        $poi->setCity( 'test town' );
        $poi->setCountry( 'test country' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setCountryCode( 'uk' );
        $poi->setLongitude( '0' );
        $poi->setLatitude( '0' );
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', 1);
        
        $poi->save();
        
        $poi = new Poi();
        $poi->setPoiName( 'test name2' );
        $poi->setStreet( 'test street2' );
        $poi->setCity( 'test town2' );
        $poi->setCountry( 'test country' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setCountryCode( 'uk' );
        $poi->setLongitude( '0' );
        $poi->setLatitude( '0' );
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategory', 1);

        $poi->save();

        $this->export = new XMLExportPOI( $this->vendor2 );

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
    }


}
?>
