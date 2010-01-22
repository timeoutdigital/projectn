<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../lib/export/XMLExport.class.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test class for XMLExport.
 * Generated by PHPUnit on 2010-01-15 at 14:15:12.
 */
class XMLExportTest extends PHPUnit_Framework_TestCase {
  /**
   * @var XMLExport
   */
    private $export;

    private $vendor2;

    private $destination;

    private $specialChars = '&<>\'"';

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
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
        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategories', array( 1, 2 ) );

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
        $poi->link('PoiCategories', 1);

        $poi->save();

        $this->xml = simplexml_load_string('
          <root>
            <node />
          </root>'
        );

        $this->destination = dirname( __FILE__ ) . '/../../export/poi/test.xml';
        //$this->export = new XMLExportPOI( $this->vendor2, $this->destination );

        $this->export = $this->getMockForAbstractClass( 'XMLExport',
          array($this->vendor2, $this->destination, 'Poi'));
      }
      catch(PDOException $e)
      {
        echo $e->getMessage();
      }
    }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroySqliteMemoryDb();
  }

  /**
   * Should throw error if
   * - first argument is not a Vendor
   * - destination is does not exist
   */
  public function testConstructor()
  {
    try
    {
      $this->getMockForAbstractClass('XMLExport', array( 'foo', $this->destination, 'Poi' ) );
      $this->fail();
    }
    catch( ExportException $e ){}
    
    try
    {
      $this->getMockForAbstractClass('XMLExport', array( new Vendor(), 'non/existant/file', 'Poi' ) );
      $this->fail();
    }
    catch( ExportException $e ){}
  }

  /**
   * @todo Implement testRun().
   */
  public function testRunCallsAbstractMethods()
  {
    $this->export->expects( $this->once() )
                 ->method( 'generateXML' )
                 ->will( $this->returnValue( $this->xml ) );

    $this->export->run();
  }

  /**
   * Test has correct export start date and time
   */
  public function testGetStartDate()
  {
    $this->export->expects( $this->once() )
                 ->method( 'generateXML' )
                 ->will( $this->returnValue( $this->xml ) );
    
    $date = date( 'Y-m-d\TH:i:s' );
    $this->export->run();
    $this->assertEquals( $date, $this->export->getStartTime() );
  }

  /**
   * check correct xml is written
   */
  public function testWriteToXMLCreatesCorrectFile()
  {
    $this->export->expects( $this->once() )
                 ->method( 'generateXML' )
                 ->will( $this->returnValue( $this->xml ) );
    
    unlink( $this->destination );
    $this->assertFileNotExists( $this->destination );

    $this->export->run();
    $this->assertFileExists( $this->destination );

    $xmlFromFile = simplexml_load_file( $this->destination );
    $this->assertTrue( $xmlFromFile instanceof SimpleXMLElement );
  }

  /**
   * check specialChars() takes care of special character, utf-8
   */
  public function testSpecialChars()
  {
    $this->assertEquals(
      htmlspecialchars( $this->specialChars, ENT_NOQUOTES, 'UTF-8' ),
      XMLExport::escapeSpecialChars( $this->specialChars )
    );
  }

}
?>
