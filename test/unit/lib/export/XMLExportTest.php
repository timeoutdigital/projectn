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
    private $object;

    private $vendor2;

    private $destination;

    private $specialChars = '&<>\'"';

    /**
     *
     * @var DOMDocument
     */
    private $domDocument;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
protected function setUp()
    {
      try {
        ProjectN_Test_Unit_Factory::createDatabases();

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
        $poi->setCountry( 'GBR' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
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
        $poi->setCountry( 'ABC' );
        $poi->setVendorPoiId( '123' );
        $poi->setLocalLanguage('en');
        $poi->setLongitude( '0.3' );
        $poi->setLatitude( '0.4' );

        $poi->link( 'Vendor', 2 );
        $poi->link('PoiCategories', 1);

        $poi->save();

        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $rootElement = $domDocument->appendChild(new DOMElement('root'));
        $rootElement->appendChild(new DOMElement('node'));
        $this->domDocument = $domDocument;

        $this->destination = dirname( __FILE__ ) . '/../../export/poi/XMLExport.xml';
        //$this->export = new XMLExportPOI( $this->vendor2, $this->destination );

        $this->object = $this->getMockForAbstractClass( 'XMLExport',
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
    ProjectN_Test_Unit_Factory::destroyDatabases();
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
    $this->object->expects( $this->once() )
                 ->method( 'mapDataToDOMDocument' )
                 ->will( $this->returnValue( $this->domDocument ) );

    $this->object->run();
  }

  /**
   * Test has correct export start date and time
   */
  public function testGetStartDate()
  {
    $this->object->expects( $this->once() )
                 ->method( 'mapDataToDOMDocument' )
                 ->will( $this->returnValue( $this->domDocument ) );

    $date = date( 'Y-m-d\TH:i:s' );
    $this->object->run();
    $this->assertEquals( $date, $this->object->getStartTime() );
  }

  /**
   * check correct xml is written
   */
  public function testWriteToXMLCreatesCorrectFile()
  {
    $this->object->expects( $this->once() )
                 ->method( 'mapDataToDOMDocument' )
                 ->will( $this->returnValue( $this->domDocument ) );

    unlink( $this->destination );
    $this->assertFileNotExists( $this->destination );

    $this->object->run();
    $this->assertFileExists( $this->destination );

    $domDocument = new DOMDocument();
    $domDocument->load( $this->destination );
    $rootNodeList = $domDocument->getElementsByTagName('root');
    $this->assertEquals( 1, $rootNodeList->length );
    $this->assertEquals('root', $rootNodeList->item(0)->nodeName);
  }

  /**
   *
   */
  public function testAppendNonRequiredElement()
  {
    $expected = new DOMDocument('1.0', 'utf-8');
    $expected->loadXML('
      <root>
      </root>
    ');
    $domDocument = new DOMDocument('1.0', 'utf-8');
    $rootElement = $domDocument->appendChild( new DOMElement( 'root' ) );
    $testElement = $this->object->appendNonRequiredElement( $rootElement, 'test', '' );
    $this->assertEqualXMLStructure( $expected, $domDocument );
  }

  public function testAppendRequiredElement()
  {
    $expected2 = new DOMDocument('1.0', 'utf-8');
    $expected2->loadXML('
      <root>
        <test>test content</test>
      </root>
    ');

    $domDocument2 = new DOMDocument('1.0', 'utf-8');
    $rootElement2 = $domDocument2->appendChild( new DOMElement( 'root' ) );

    $testElement2 = $this->object->appendRequiredElement( $rootElement2, 'test', 'test content' );

    $this->assertEquals( 'test', $testElement2->nodeName );
    $this->assertEquals( 'test content', $testElement2->nodeValue );
    $this->assertEqualXMLStructure( $expected2, $domDocument2 );

    $expected = new DOMDocument('1.0', 'utf-8');
    $expected->loadXML('
      <root>
        <test></test>
      </root>
    ');

    $domDocument = new DOMDocument('1.0', 'utf-8');
    $rootElement = $domDocument->appendChild( new DOMElement( 'root' ) );
    $testElement = $this->object->appendRequiredElement( $rootElement, 'test', null );

    $this->assertEquals( 'test', $testElement->nodeName );
    $this->assertEquals( '', $testElement->nodeValue );
    $this->assertEqualXMLStructure( $expected, $domDocument );

    $expected = new DOMDocument('1.0', 'utf-8');
    $expected->loadXML('
      <root>
        <test><![CDATA[test content]]></test>
      </root>
    ');
    $domDocument = new DOMDocument('1.0', 'utf-8');
    $rootElement = $domDocument->appendChild( new DOMElement( 'root' ) );

    $testElement = $this->object->appendRequiredElement(
      $rootElement, 'test', 'some content', XMLExport::USE_CDATA );

    $this->assertEquals( 'test', $testElement->nodeName );
    $this->assertEqualXMLStructure( $expected, $domDocument );

  }
}
?>
