<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of Russia Feed Events Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class RussiaFeedEventsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var SimpleXMLElement
   */
  protected $eventsXml;

  /**
   * @var RussiaFeedEventsMapper
   */
  protected $dataMapper;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

    private function _getParams( $filename )
    {
        return array(
            'type' => 'event',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/russia/' . $filename
             ),
        );
    }

  public function testMapEvents()
  {
    $this->importFileAndAddRequiredPois();

    $events = Doctrine::getTable('Event')->findAll();
    $this->assertEquals( 10, $events->count(), 'Should have same number of events imported as in the feed received.' );
    $event = $events[0];

    $this->assertEquals( '2008-10-27', $event['review_date'] );
    $this->assertEquals( 373,  $event['vendor_event_id'] );
    $this->assertEquals( 'Двое на качелях', $event['name'] );
    $this->assertEquals( 'Спектакль о двух одиноких людях, потерявших опору в жизни. Отношения их складываются непросто, но нахлынувшее чувство позволяет вновь обрести вкус к жизни.', $event['short_description'] );
    $this->assertEquals( 'Спектакль', mb_substr( $event['description'], 0, 9, "UTF-8" ) );

    $this->assertEquals( 'ru', $event->Vendor->language );

    $this->assertGreaterThan( 0, $event[ 'VendorEventCategory' ]->count(), 'Should have more that one VendorEventCategory' );
    $this->assertEquals( "Драма | Театр", $event[ 'VendorEventCategory' ][ 'Драма | Театр' ][ 'name' ], 'Check event category' );

    $this->assertGreaterThan( 0, $event[ 'EventMedia' ]->count(), 'Should have more than one media' );
    $this->assertEquals( "http://pix.timeout.ru/63530.jpeg", $event[ 'EventMedia' ][0]['url'], 'Check media url' );

    $this->assertGreaterThan( 0, $event[ 'EventOccurrence' ]->count() );
    $this->assertEquals( 4630349, $event[ 'EventOccurrence' ][0]['vendor_event_occurrence_id'] );

    // validate Occurrences
    $occurrence = $event[ 'EventOccurrence' ][0];
    $this->assertEquals('2010-05-21' , $occurrence['start_date']);
    $this->assertEquals('19:00' , $occurrence['start_time']);
    
    $this->assertEquals( null , $occurrence['end_date']);
    $this->assertEquals('19:10' , $occurrence['end_time']);

    // Empty Start / End Time
    $occurrence = $event[ 'EventOccurrence' ][1];
    $this->assertEquals('2010-05-21' , $occurrence['start_date']);
    $this->assertEquals(null , $occurrence['start_time']);

    $this->assertEquals('2010-05-21' , $occurrence['end_date']);
    $this->assertEquals(null , $occurrence['end_time']);

    /*
     * #807 - This tests are invalid as We only store Unique Occurrences ( start_date . start_time . poi_id )
     *
     // End Date Greater
    $occurrence = $event[ 'EventOccurrence' ][2];
    $this->assertEquals('2010-05-21' , $occurrence['start_date']);
    $this->assertEquals('19:00' , $occurrence['start_time']);

    $this->assertEquals('2010-05-22' , $occurrence['end_date']);
    $this->assertEquals('19:00' , $occurrence['end_time']);

    // No End Date but End Time = Start time
    $occurrence = $event[ 'EventOccurrence' ][3];
    $this->assertEquals('2010-05-21' , $occurrence['start_date']);
    $this->assertEquals('19:00' , $occurrence['start_time']);

    $this->assertEquals('' , $occurrence['end_date']);
    $this->assertEquals(null , $occurrence['end_time']);

    // No End date but End Time is Greater then Start time
    $occurrence = $event[ 'EventOccurrence' ][4];
    $this->assertEquals('2010-5-21' , $occurrence['start_date']); // 0 Removed from month to check DateTime Formating working when checking time
    $this->assertEquals('19:00' , $occurrence['start_time']);

    $this->assertEquals('' , $occurrence['end_date']);
    $this->assertEquals('19:10' , $occurrence['end_time']);
     */
  }

  

  public function testVenueIdsCreated()
  {
    $this->importFileAndAddRequiredPois();
    $this->assertEquals( 7, Doctrine::getTable( 'Poi' )->count() );
  }

  public function testThereIsNoRepeatedOccurrences ()
  {
    $this->importFileAndAddRequiredPois( 'russia_events_multiple_occurrences.short.xml' );

    $this->markTestSkipped( 'Event.class->removeMultipleOccurrences() should be called in apply Fix to test this part of the Test' );
    
    //check that event with the id 128382 is imported
    //this is the event that has duplicated occurrences. two occurrence2 for 2010-09-14
    //test for same star
     $event = Doctrine::getTable('Event')->findOneByVendorEventId( 128382 );

     $occurrenceDates = array();

     foreach ($event[ 'EventOccurrence' ] as $occurrence)
     {
        $occurrenceDates [] = $occurrence[ 'start_date' ];
     }

     $this->assertEquals( 6, count( $occurrenceDates )  );
     //occurrence_dates array shouldn't have any duplicate values even tho the feed has a duplicate occurrence!
     $this->assertEquals( count( $occurrenceDates ), count( array_unique( $occurrenceDates )  ) ,'Duplicate occurrences should be removed while saving an event. check event model'  );

     //test for same date and time but different venues
     $event = Doctrine::getTable('Event')->findOneByVendorEventId( 128388 );

     $occurrenceDates = array();

     foreach ($event[ 'EventOccurrence' ] as $occurrence)
     {
        $occurrenceDates [  ] = $occurrence[ 'start_date' ];
     }

     $this->assertEquals( count( $event[ 'EventOccurrence' ]  ), 3 , '3 occurrences' );
     $this->assertEquals( count( array_unique( $occurrenceDates ) ), 2 , '3 occurrences but happening in 2 days ' );
     $this->assertEquals( $event[ 'EventOccurrence' ][0]['start_date'], $event[ 'EventOccurrence' ][2]['start_date'] , '1st and 3rd one happining in the same day' );


     //test for same date and venue but different times
     $event = Doctrine::getTable('Event')->findOneByVendorEventId( 128389 );

     $occurrenceDates = array();

     foreach ($event[ 'EventOccurrence' ] as $occurrence)
     {
        $occurrenceDates [  ] = $occurrence[ 'start_date' ];
     }

     $this->assertEquals( count( $event[ 'EventOccurrence' ]  ), 3 , '3 occurrences' );
     $this->assertEquals( count( array_unique( $occurrenceDates ) ), 2 , '3 occurrences but happening in 2 days ' );
     $this->assertEquals( $event[ 'EventOccurrence' ][0]['start_date'], $event[ 'EventOccurrence' ][2]['start_date'] , '1st and 3rd one happining in the same day' );
     $this->assertNotEquals( $event[ 'EventOccurrence' ][0]['start_time'], $event[ 'EventOccurrence' ][2]['start_time'] , '1st and 3rd one happining in the same day  but different times' );
     $this->assertEquals( $event[ 'EventOccurrence' ][0]['poi_id'], $event[ 'EventOccurrence' ][2]['poi_id'] , '1st and 3rd one happining in the same poi' );

  }


  /**
   * Private function for TEST
   */

  
  private function importFileAndAddRequiredPois( $file = 'russia_events.short.xml' )
  {
      $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'moscow' );

      $this->eventsXml = simplexml_load_file( TO_TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'russia' . DIRECTORY_SEPARATOR . $file );

    $this->createVenuesFromVenueIds( $this->getVenueIdsFromXml() );

    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedEventsMapper( $this->vendor, $this->_getParams( $file ) ) );
    $importer->run();
  }

  private function getVenueIdsFromXml()
  {
    $venues = $this->eventsXml->xpath('//venue');
    $venueIds = array();

    foreach( $venues as $venue )
    {
      $venueIds[] = (string) $venue;
    }

    $venueIds = array_unique( $venueIds );

    //make sure our keys are not missing a number
    sort( $venueIds );

    return $venueIds;
  }
  
  private function createVenuesFromVenueIds( $venueIds )
  {
    for( $i = 0; $i < count( $venueIds ); $i++ )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'vendor_poi_id' ] = $venueIds[ $i ];
      $poi[ 'Vendor' ] = $this->vendor;
      $poi->save();
    }
  }

}
?>
