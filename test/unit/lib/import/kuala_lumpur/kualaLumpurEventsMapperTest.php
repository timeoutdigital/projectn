<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Events mapper
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class kualaLumpurEventsMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en',
      'inernational_dial_code' => '+60',
      ) );
    $this->addPoisWithIds( array( 509, 208, 216, 450, 1084 ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_events.xml' );
    $this->runImport();

    $this->events = Doctrine::getTable( 'Event' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $this->assertEquals( 4,
                         $this->events->count(),
                         'check import count is 4. 5 in the feed, but one is a movie.'
                         );

    $this->assertEquals( 5158,
                         $this->events[0]['vendor_event_id'],
                         'Check id'
                         );

    $this->assertEquals( 'Deepak Menon Film Showing and Conversations',
                         $this->events[0]['name'],
                         'Check name'
                         );

    $this->assertEquals( 'http://www.instantcafetheatre.com/',
                         $this->events[0]['url'],
                         'Check url'
                         );
 
    $this->assertEquals( 'FREE entry for Ladies / RM35 Men (Inc 1 drink)',
                         $this->events[0]['price'],
                         'Check price'
                         );

    $this->assertEquals( '2010-05-07 00:00:00',
                         $this->events[0]['EventOccurrence'][0]['start_date'],
                         'Check start date'
                         );

    $this->assertEquals( '20:30:00',
                         $this->events[0]['EventOccurrence'][0]['start_time'],
                         'Check start time'
                         );

    $this->assertEquals( '2010-05-08 00:00:00',
                         $this->events[0]['EventOccurrence'][0]['end_date'],
                         'Check end date'
                         );
  }

  public function testVendorCategories()
  {
    $this->assertEquals( 'Film | Screenings',
                                                                //should be able to use integer index!
                         $this->events[0]['VendorEventCategory']['Film | Screenings']['name'],
                         'Check vendor category'
                         );

    $this->assertEquals( 'Music | Gigs',
                                                                //should be able to use integer index!
                         $this->events[1]['VendorEventCategory']['Music | Gigs']['name'],
                         'Check vendor category'
                         );
  }

  public function testDescriptions()
  {
    $this->assertEquals( 'A preview of the festival, right here in the Klang Valley. Featuring an awesome mash up of musical talents, the night will start will songstress...',
                         $this->events[1]['short_description'],
                         'Checking short description'
                          );

    $desc = <<<EOF
<p>It's finally here. The Miri International Jazz Festival make its highly anticipated return. To whet your appetite, Laundry Bar brings to you a preview of the festival, right here in the Klang Valley.<br />
<br />
Featuring an awesome mash up of musical talents, the night will start will songstress Diandra Arjunaidi, followed by Isaac Entry and Jeremy Tordjman (pic). Catch these amazing talents in action live at Laundry Bar on May 7, 9.30pm.</p>
EOF;

    $this->assertEquals( $desc,
                         $this->events[1]['description'],
                         'Checking long description'
                          );
  }

  public function testImage()
  {
    $this->assertEquals( 'http://www.timeoutkl.com/uploadfiles/image/Events/Music/Big/bigimg_MiriIntJazzFest_Preview.jpg',
                          $this->events[1]['EventMedia'][0]['url'],
                          'Checking event media'
                          );
  }

  public function testOccurrence()
  {
    $this->assertEquals( 1,
                         count( $this->events[0]['EventOccurrence'] ),
                         'check occurrence count'
                         );
    $this->runImport();

    $events = Doctrine::getTable( 'Event' )->findAll();
    $this->assertEquals( 1,
                         count( $events[0]['EventOccurrence'] ),
                         'check occurrence count'
                         );
  }

  private function runImport()
  {
    $importer = new Importer();
    $importer->addDataMapper( new kualaLumpurEventsMapper( $this->vendor, $this->xml ) );
    //$importer->addLogger( new echoingLogger( ));
    $importer->run();
  }

  private function addPoisWithIds( $ids )
  {
    foreach( $ids as $id )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'Vendor' ] = $this->vendor;
      $poi[ 'vendor_poi_id' ] = $id;
      $poi->save();
    }
    $this->assertEquals( count( $ids ), Doctrine::getTable( 'Poi' )->count() );
  }
}
