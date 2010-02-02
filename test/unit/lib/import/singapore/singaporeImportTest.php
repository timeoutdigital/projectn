<?php
require_once 'PHPUnit/Framework.php';

require_once dirname( __FILE__ ).'/../../../bootstrap.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test singapore import.
 *
 * @package test
 * @subpackage lib.unit.import.singapore
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class singaporeImportTest extends PHPUnit_Framework_TestCase {
  /**
   * @var singaporeImport
   */
  protected $object;

  protected $xmlObj;

  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var SimpleXmlElement
   */
  protected $dataXMLObject;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {

   setlocale(LC_MONETARY, 'en_US.UTF-8');

   ProjectN_Test_Unit_Factory::createDatabases();

   Doctrine::loadData('data/fixtures');
   
   $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('singapore', 'en-US');

   $this->dataXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore_weekly_events.xml' );

   $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore_event_detail.xml' );   
   $stubCurlImporter = $this->getMock( 'curlImporter' );
   $stubCurlImporter->expects( $this->any() )
                     ->method( 'pullXML' );
   $stubCurlImporter->expects( $this->any() )
                     ->method( 'getXml' )
                     ->will( $this->returnValue( $stubReturnXMLObject ) );

   $this->object = new singaporeImport( $this->dataXMLObject, $this->vendorObj, $stubCurlImporter );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   *
   */
  public function testInsertCategoriesPoisEvents()
  {    
    $this->assertTrue( $this->object->insertCategoriesPoisEvents() );

    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( 801 );

    $this->assertEquals( 'Singapore Botanic Gardens', $poi[ 'poi_name' ] );

  }

  /*
   *
   */
  public function testFetchPoiAndPoiCategory()
  {
   $this->assertTrue( $this->object->fetchPoiAndPoiCategory( 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?event=8355&key=ffab6a24c60f562ecf705130a36c1d1e' ) instanceof SimpleXMLElement );

  }

}
?>
