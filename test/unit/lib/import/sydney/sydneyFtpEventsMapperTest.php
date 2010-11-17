<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';
/**
 * Test class for sydney venues import
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 *
 * @version 1.0.1
 */
class sydneyFtpEventsMapperTest extends PHPUnit_Framework_TestCase
{

    private $vendor;
    private $params;
    private $xmlNodesTmpPath;
    
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    // Dynamicaly Change Date on events
    $xmlFilePath = TO_TEST_DATA_PATH . '/sydney/sydney_sample_events.xml';
    $xmlNodes = simplexml_load_file( $xmlFilePath );
    $xmlNodes = $this->setDynamicTime( $xmlNodes );

    // save in TMP folder and Remove on TearDown
    $this->xmlNodesTmpPath = tempnam( '/tmp', 'xml' );
    file_put_contents( $this->xmlNodesTmpPath , $xmlNodes->saveXML() );

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('sydney');
    $this->params = array( 'type' => 'event', 'ftp' => array(
                                                        'classname' => 'FTPClientMock',
                                                        'username' => 'test',
                                                        'password' => 'test',
                                                        'src' => '',
                                                        'dir' => '/',
                                                        'file' => $this->xmlNodesTmpPath,
                                                        )
        );
    //event feed has pois with vendor_poi_id 1,2 and 3
    for ( $i =1; $i< 4; $i++ )
    {
        $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $poi[ 'vendor_poi_id' ] = $i;
        $poi[ 'Vendor' ] = $this->vendor;
        $poi->save();
    }

    //Import data
    $importer = new Importer();
    $importer->addDataMapper( new sydneyFtpEventsMapper( $this->vendor, $this->params ) );
    $importer->run();
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    unlink( $this->xmlNodesTmpPath ); // remove TMP file
  }

  public function testMapping()
  {

    $this->assertGreaterThan( 1,
                         Doctrine::getTable( 'Event' )->findAll()->count(),
                        'Database should have same more then 1 Poi'
                         );

    $event = Doctrine::getTable( 'Event' )->findOneById( 1 );

    $this->assertEquals('a good festival', $event['name'], 'Check name field.' );
    $this->assertEquals('2010-03-29 09:59:00', $event['review_date'], 'Check review_date field.' );
    $this->assertEquals('1891484e2', $event['vendor_event_id'], 'Check vendor_event_id field.' );
    $this->assertEquals('Sydney Leather Pride Association brings Easter to a grinding halt with this down and dirty party at Saddlebar. DJs George Roussos, Sveta and Rob Davis kick the afternoon off and see the leather and fetish geared up crowd working the dance floor until midnight.', $event['description'], 'Check description field.' );
    $this->assertEquals('http://www.somewebsite.com', $event['url'], 'Check url field.' );
    $this->assertEquals('$30.00', $event['price'], 'Check price field.' );
    $this->assertEquals('1', $event['rating'], 'Check rating field.' );
    $this->assertEquals('8', $event['vendor_id'], 'Check vendor_id field.' );
  }

  public function testVendorCategory()
  {
    $events = Doctrine::getTable( 'Event' )->findAll( );

    $this->assertEquals( 1, count(  $events[0]['VendorEventCategory'] ),'1st event in the feed has only one vendorCategory' );

    $vendorCategory =  $events[0]['VendorEventCategory']->toArray();

    $this->assertEquals( 'Gay & Lesbian',   $vendorCategory[ 'Gay & Lesbian' ]['name']  );

    $vendorCategory =  $events[1]['VendorEventCategory']->toArray();

  }

  public function testProperties()
  {
    $events = Doctrine::getTable( 'Event' )->findAll( );

    $this->assertNull( $events[0]['CriticsChoiceProperty'] );
    $this->assertNull( $events[0]['RecommendedProperty'] );
    $this->assertNull( $events[0]['FreeProperty'] );

    $this->assertNull( $events[1]['CriticsChoiceProperty'] );
    $this->assertEquals( 'Y', $events[1]['RecommendedProperty'] );
    $this->assertNull( $events[1]['FreeProperty'] );
  }


  public function testEventOccurrence()
  {
      $event = Doctrine::getTable( 'Event' )->findOneById( 1 );
      $this->assertEquals( 2, $event['EventOccurrence']->count(), 'Since This event repeated with different date as Occurrence, this occurrence count should be 2' );
  }

  public function testHasImage()
  {
    $event = Doctrine::getTable( 'Event' )->findOneById( 1 );

    $this->assertEquals( 'http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg',
                          $event['EventMedia'][0]['url']
                          );
  }


  private function setDynamicTime( SimpleXMLElement $xmlNodes )
  {

      $xmlNodes->event[0]->DateInserted = date('d/m/Y h:i:s A');
      $xmlNodes->event[0]->DateFrom = date('d/m/Y h:i:s A');
      $xmlNodes->event[0]->DateTo = date('d/m/Y h:i:s A', strtotime( ' +1 day' ) );

      $xmlNodes->event[2]->DateInserted = date('d/m/Y h:i:s A');
      $xmlNodes->event[2]->DateFrom = date('d/m/Y h:i:s A');
      $xmlNodes->event[2]->DateTo = date('d/m/Y h:i:s A', strtotime( ' +1 day' ) );

      $xmlNodes->event[3]->DateInserted = date('d/m/Y h:i:s A');
      $xmlNodes->event[3]->DateFrom = date('d/m/Y h:i:s A', strtotime( ' +5 day' ));
      $xmlNodes->event[3]->DateTo = date('d/m/Y h:i:s A', strtotime( ' +6 day' ) );

      return $xmlNodes;
  }

  public function testFilmEventsAreSavedInArtCategory()
  {
      $event = Doctrine::getTable( 'Event' )->findOneById( 3 );  //event's category is Film but we are expecting it to be saved as Art

      $vendorCategory =  $event['VendorEventCategory']->toArray();

      $this->assertEquals( 'Art', $vendorCategory[ 'Art' ][ 'name' ] );

  }

  public function testSimulateUpdateAndTestNoDuplicateOccurrences()
  {
      // Existing count
      $totalEventsWasInDB = Doctrine::getTable( 'Event' )->findAll()->count();
      $totalOccurrencesWasInDB = Doctrine::getTable( 'EventOccurrence' )->findAll()->count();
      
      //Import data
      $importer = new Importer();
      $importer->addDataMapper( new sydneyFtpEventsMapper( $this->vendor, $this->params ) );
      $importer->run();
      
      $this->assertEquals( $totalEventsWasInDB, Doctrine::getTable( 'Event' )->findAll()->count());
      $this->assertEquals( $totalOccurrencesWasInDB, Doctrine::getTable( 'EventOccurrence' )->findAll()->count());


  }
}
