<?php
require_once 'PHPUnit/Framework.php';

require_once dirname( __FILE__ ).'/../../../bootstrap.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test singapore import.
 *
 * @package test
 * @subpackage singapore.import.lib
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
   * @var curlImporter
   */
  protected $stubCurlImporter;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {

   setlocale(LC_MONETARY, 'en_US.UTF-8');

   ProjectN_Test_Unit_Factory::createDatabases();

   Doctrine::loadData('data/fixtures');
   
   $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('singapore', 'en-US');

   $this->stubCurlImporter = $this->getMock( 'curlImporter' );
   $this->stubCurlImporter->expects( $this->any() )->method( 'pullXML' );

   $this->object = new singaporeImportTestVersion( $this->vendorObj, $this->stubCurlImporter );

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
  public function testInsertPoisAndInsertPoi()
  {
     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/all_of_singapore_full_venues_list.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );

     $this->object->insertPois( $xmlObj );

     $poisCol = Doctrine::getTable( 'Poi' )->findAll();

     $this->assertEquals( 1, $poisCol->count() );
  }
  
  /*
   *
   */
  public function testInsertEventsAndInsertEvent()
  {


     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $stubCurlImporterDetail->getXml();

     $this->object->insertPoi( $xmlObj );

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/all_of_singapore_full_events_list.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/event_detail.xml' );
     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );

     $this->object->insertEvents( $xmlObj );

     $eventsCol = Doctrine::getTable( 'Event' )->findAll();

     $this->assertEquals( 1, $eventsCol->count() );

     $this->assertEquals( 1, count( $eventsCol[ 0 ][ 'EventOccurrence' ] ) );
  }



  /*
   *
   */
   public function testFetchDetailUrl()
   {
     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $returnXml = $this->object->fetchDetailUrl( 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue=2154&key=ffab6a24c60f562ecf705130a36c1d1e' );

     $this->assertEquals( $stubReturnXMLObject, $returnXml );
   }

}


class singaporeImportTestVersion extends singaporeImport
{
  public function setCurlImporter( $curlImporter )
  {
    $this->_curlImporter = $curlImporter;
  }
}

?>
