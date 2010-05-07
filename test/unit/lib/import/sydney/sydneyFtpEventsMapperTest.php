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

    $this->feed   = simplexml_load_file( TO_TEST_DATA_PATH . '/sydney_sample_events.xml' );
    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor',  array( 
                                                     'city'          => 'sydney', 
                                                     'language'      => 'en-AU', 
                                                     'country_code'  => 'AUS', 
                                                     'inernational_dial_code'  => '+61', 
                                                     ) );

    ProjectN_Test_Unit_Factory::add( 'Poi' );

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
    $this->assertEquals( 2,
                         $this->eventTable->count(),
                        'Database should have same number of Events as feed after import'
                         );

    $event = $this->eventTable->findOneById( 1 );

    $this->assertEquals('2010-03-29 10:01:00', $event['review_date'], 'Check review_date field.' );
    $this->assertEquals('a833570ea', $event['vendor_event_id'], 'Check vendor_event_id field.' );
    $this->assertEquals('Ascension', $event['name'], 'Check name field.' );
    $this->assertEquals('Sydney Leather Pride Association brings Easter to a grinding halt with this down and dirty party at Saddlebar. DJs George Roussos, Sveta and Rob Davis kick the afternoon off and see the leather and fetish geared up crowd working the dance floor until midnight.', $event['description'], 'Check description field.' );
    $this->assertEquals('http://www.somewebsite.com', $event['url'], 'Check url field.' );
    $this->assertEquals('30.00', $event['price'], 'Check price field.' );
    $this->assertEquals('1', $event['rating'], 'Check rating field.' );
    $this->assertEquals('1', $event['vendor_id'], 'Check vendor_id field.' );
  }

  public function testVendorCategory()
  {
    $events = $this->eventTable->findAll( );

    $this->assertEquals( 'Gay & Lesbian',
                          $events[0]['VendorEventCategory']['Gay & Lesbian']['name']
                          );

    $this->assertEquals( 'Gay & Lesbian | Club',
                          $events[1]['VendorEventCategory']['Gay & Lesbian | Club']['name']
                          );
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

  public function testMedia()
  {
    $this->markTestSkipped();
    $event = $this->eventTable->findOneById( 1 );
    $this->assertEquals('http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg', $event['EventMedia'][0]['url'] );
  }

  public function testEventOccurrence()
  {
      $event = $this->eventTable->findOneById( 1 );
      $this->assertEquals( 1, $event['EventOccurrence']->count() );
  }

  
}
