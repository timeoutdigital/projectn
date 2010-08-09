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


  //protected $xmlObj;

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
   *
   * @var logImport
   */
  protected $logger;


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

    $this->logger = new logImport( $this->vendorObj );

    $this->object = new singaporeImportTestVersion( $this->vendorObj, $this->stubCurlImporter, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
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
   * testPublicTransportDoesNotStartWithASpaceOrPipeCharacter
   *
   * @todo add test if fields mapped correctly
   */
  public function testPublicTransportDoesNotStartWithASpaceOrPipeCharacter()
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

     $pois = Doctrine::getTable( 'Poi' )->findAll();
     $firstPoi = $pois->getFirst();
     $transportFirstCharacter = substr( $firstPoi['public_transport_links'], 0, 1 );

     $this->assertNotEquals( " ", $transportFirstCharacter, "Public Transport Links should not start with a space." );
     $this->assertNotEquals( "|", $transportFirstCharacter, "Public Transport Links should not start with a pipe." );
  }

  /*
   * testInsertPoisAndInsertPoi
   * @todo add test if fields mapped correctly
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
   * testInsertEventsAndInsertEvent
   * @todo add test if fields mapped correctly
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

     $this->assertEquals( 4, count( $eventsCol[ 0 ][ 'EventOccurrence' ] ) );

  }

  /*
   * testInsertMoviesAndInsertMovie
   *
   * test with empty data condition
   * @todo add test if fields mapped correctly
   */
  public function testInsertMoviesAndInsertMovie()
  {
     ;

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/all_of_singapore_full_movies_list.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/movie_detail.xml' );

     $stubReturnXMLObject->opens = '';
     $stubReturnXMLObject->data_add = '';

     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );
     $this->object->insertMovies( $xmlObj );
     $moviesCol = Doctrine::getTable( 'Movie' )->findAll();
     $this->assertEquals( 1, $moviesCol->count() );

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/movie_detail.xml' );
  }



  /*
   * testInsertMoviesAndInsertMovie
   *
   * test with -60 days condition (last day valid)
   * @todo add test if fields mapped correctly
   */
  public function testInsertMoviesAndInsertMovie2()
  {


     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/all_of_singapore_full_movies_list.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/movie_detail.xml' );

     $stubReturnXMLObject->opens = '';
     $stubReturnXMLObject->data_add = date( 'Y-m-d', strtotime( '-60 days' ) );

     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );
     $this->object->insertMovies( $xmlObj );
     $moviesCol = Doctrine::getTable( 'Movie' )->findAll();
     $this->assertEquals( 1, $moviesCol->count() );
  }

  /*
   * testInsertMoviesAndInsertMovie
   *
   * test with -61 expired date condition
   * @todo add test if fields mapped correctly
   */
  public function testInsertMoviesAndInsertMovie3()
  {
     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/all_of_singapore_full_movies_list.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/movie_detail.xml' );

     $stubReturnXMLObject->opens = date( 'Y-m-d', strtotime( '-61 days' ) );
     $stubReturnXMLObject->data_add = '';

     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );
     $this->object->insertMovies( $xmlObj );
     $moviesCol = Doctrine::getTable( 'Movie' )->findAll();
     $this->assertEquals( 0, $moviesCol->count() );
  }



  /*
   * testFetchDetailUrl
   */
   public function testFetchDetailUrl()
   {
     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $this->stubCurlImporter->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $this->stubCurlImporter->getXml();

     $returnXml = $this->object->fetchDetailUrl( 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue=2154&key=ffab6a24c60f562ecf705130a36c1d1e' );

     $this->assertEquals( $stubReturnXMLObject, $returnXml );
   }

   /*
    * testExtractSingaporeAgeRatingCode
    */
   public function testExtractSingaporeAgeRatingCode()
   {
     $ageRatingCode = $this->object->extractSingaporeAgeRatingCode( 'NC16 - No children under the age of 16' );
     $this->assertEquals( 'NC16', $ageRatingCode );
   }

  /*
   * testTryToInsertMissingPoi
   */
  public function testTryToInsertMissingPoi()
  {

     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $stubCurlImporterDetail->getXml();

     // this is needed just for testing
     $this->object->setCurlImporter( $stubCurlImporterDetail );

     $this->object->tryToInsertMissingPoi( 2154 );

     $poisCol = Doctrine::getTable( 'Poi' )->findAll();

     $this->assertEquals( 1, $poisCol->count() );
  }

  /**
   * test if attribute is appended
   *
   */
  public function testCriticsPicksPropertyOnEvent()
  {
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

     $eventObj = Doctrine::getTable('Event')->findOneByVendorEventId(8529);

    //Test that the Critics_choice is normalized to this form
    $this->assertEquals( 'Critics_choice', $eventObj['EventProperty'][0]['lookup'], "Use 'Critics_choice' instead of 'Critic's Pick' or whatever else.");
  }

  /**
   * test if attribute is appended
   *
   */
  public function testCriticsPicksPropertyOnPoi()
  {
     $stubReturnXMLObject = simplexml_load_file( dirname(__FILE__).'/../../../data/singapore/venue_detail.xml' );
     $stubCurlImporterDetail = $this->getMock( 'curlImporter' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'pullXML' );
     $stubCurlImporterDetail->expects( $this->any() )->method( 'getXml' )->will( $this->returnValue( $stubReturnXMLObject ) );
     $xmlObj = $stubCurlImporterDetail->getXml();

     $this->object->insertPoi( $xmlObj );

     $poiObj = Doctrine::getTable('Poi')->findOneByVendorPoiId('2154');

     $this->assertEquals( 'Critics_choice', $poiObj['PoiProperty'][0]['lookup'], "Use 'Critics_choice' instead of 'Critic's Pick' or whatever else.");
   }
}


class singaporeImportTestVersion extends singaporeImport
{
  public function setCurlImporter( $curlImporter )
  {
    $this->_curlImporter = $curlImporter;
  }

  protected function addImageHelper( Doctrine_Record $storeObject, SimpleXMLElement $element ) {
      return;
  }
}

?>
