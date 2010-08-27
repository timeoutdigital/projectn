<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for sydney venues import
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 *
 * @version 1.0.1
 */
class sydneyFtpEventsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var SimpleXMLElement
   */
  private $feed;

  /**
   * @var Vendor
   */
  private $vendor;

  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->feed   = $this->setDynamicTime( simplexml_load_file( TO_TEST_DATA_PATH . '/sydney_sample_events.xml' ) );
    
    $this->vendor =  ProjectN_Test_Unit_Factory::add( 'Vendor',  array(
                                                     'city'          => 'sydney',
                                                     'language'      => 'en-AU',
                                                     'country_code'  => 'au',
                                                     'country_code_long'  => 'AUS',
                                                     'inernational_dial_code'  => '+61',
                                                     ) );

    //event feed has pois with vendor_poi_id 1,2 and 3
    for ( $i =1; $i< 4; $i++ )
    {
        $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $poi[ 'vendor_poi_id' ] = $i;
        $poi[ 'Vendor' ] = $this->vendor;
        $poi->save();
    }
    
    $importer = new Importer();
    $importer->addDataMapper( new sydneyFtpEventsMapper( $this->vendor, $this->feed ) );
    $importer->run();

    $this->eventTable = Doctrine::getTable( 'Event' );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {

    $this->assertGreaterThan( 1,
                         $this->eventTable->count(),
                        'Database should have same more then 1 Poi'
                         );

    $event = $this->eventTable->findOneById( 1 );

    $this->assertEquals('a good festival', $event['name'], 'Check name field.' );
    $this->assertEquals('2010-03-29 09:59:00', $event['review_date'], 'Check review_date field.' );
    $this->assertEquals('1891484e2', $event['vendor_event_id'], 'Check vendor_event_id field.' );
    $this->assertEquals('Sydney Leather Pride Association brings Easter to a grinding halt with this down and dirty party at Saddlebar. DJs George Roussos, Sveta and Rob Davis kick the afternoon off and see the leather and fetish geared up crowd working the dance floor until midnight.', $event['description'], 'Check description field.' );
    $this->assertEquals('http://www.somewebsite.com', $event['url'], 'Check url field.' );
    $this->assertEquals('$30.00', $event['price'], 'Check price field.' );
    $this->assertEquals('1', $event['rating'], 'Check rating field.' );
    $this->assertEquals('1', $event['vendor_id'], 'Check vendor_id field.' );
  }

  public function testVendorCategory()
  {
    $events = $this->eventTable->findAll( );

    $this->assertEquals( 1, count(  $events[0]['VendorEventCategory'] ),'1st event in the feed has only one vendorCategory' );

    $vendorCategory =  $events[0]['VendorEventCategory']->toArray();

    $this->assertEquals( 'Gay & Lesbian',   $vendorCategory['Gay & Lesbian']['name']  );

    $vendorCategory =  $events[1]['VendorEventCategory']->toArray();

  }

  public function testProperties()
  {
    $events = $this->eventTable->findAll( );

    $this->assertNull( $events[0]['CriticsChoiceProperty'] );
    $this->assertNull( $events[0]['RecommendedProperty'] );
    $this->assertNull( $events[0]['FreeProperty'] );

    $this->assertNull( $events[1]['CriticsChoiceProperty'] );
    $this->assertEquals( 'Y', $events[1]['RecommendedProperty'] );
    $this->assertNull( $events[1]['FreeProperty'] );
  }


  public function testEventOccurrence()
  {
      $event = $this->eventTable->findOneById( 1 );
      $this->assertEquals( 1, $event['EventOccurrence']->count() );
  }

  public function testHasImage()
  {
    $event = $this->eventTable->findOneById( 1 );

    $this->assertEquals( 'http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg',
                          $event['EventMedia'][0]['url']
                          );
  }

  private function setDynamicTime( SimpleXMLElement $xmlNodes )
  {
      
      $xmlNodes->event[0]->DateFrom = date('d/m/Y h:i:s A');
      $xmlNodes->event[0]->DateTo = date('d/m/Y h:i:s A', strtotime( ' +1 day' ) );

      return $xmlNodes;
  }
}
