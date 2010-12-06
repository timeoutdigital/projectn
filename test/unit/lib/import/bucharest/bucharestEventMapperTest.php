<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class bucharestEventMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $params;

    public function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'bucharest' );
        $this->params = array( 'type' => 'event', 'curl' => array( 'classname' => 'CurlMock', 'src' => TO_TEST_DATA_PATH . '/bucharest/events.xml' ) );

        $this->_dummyPoi();
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapEvents()
    {
        // Run importer
        $importer = new Importer( );
        $importer->addDataMapper( new bucharestEventMapper( $this->vendor, $this->params ) );
        $importer->run();

        // assert results
        $this->assertEquals( 5, Doctrine::getTable( 'Poi' )->count() );

        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 5, $events->count() );

        $event = $events[0];
        $this->assertEquals( '7773314', $event['vendor_event_id']);
        $this->assertEquals( 'Steve Vai & Evolution Tempo Orchestra', $event['name']);
        $this->assertStringStartsWith( 'Exista un zvon care circula pe la Berklee (una dintre cele mai bune facultati de muzica) cum ca Steve ar fi chiulit ', $event['description']);

        $this->assertEquals( 1, $event['VendorEventCategory']->count());
        $this->assertEquals( 'Muzica', $event['VendorEventCategory']['Muzica']['name'] );
        $this->assertTrue( $event['VendorEventCategory']['Muzica']->exists() );

        $this->assertEquals( 1, $event['EventOccurrence']->count());
        $this->assertEquals( '2010-12-08', $event['EventOccurrence'][0]['start_date'] );
        $this->assertEquals( '19:30:00', $event['EventOccurrence'][0]['start_time'] );
        $this->assertEquals( null, $event['EventOccurrence'][0]['end_time'] );
        $this->assertEquals( null, $event['EventOccurrence'][0]['end_date'] );

        $event = $events[2];
        $this->assertEquals( '7773272', $event['vendor_event_id']);
        $this->assertEquals( 'Festival: CineMAiubit', $event['name']);
        $this->assertStringStartsWith( 'A 14-a editie a Festivalului International de Film Studentesc CineMAiubit isi', $event['description']);

        // Test this category to make sure that "FILM" converted to ART in Event Model, as Nokia do not accept Film in Event
        $this->assertEquals( 1, $event['VendorEventCategory']->count());
        $this->assertEquals( 'Art', $event['VendorEventCategory']['Art']['name'] );
        $this->assertTrue( $event['VendorEventCategory']['Art']->exists() );

        $this->assertEquals( 1, $event['EventOccurrence']->count());
        $this->assertEquals( '2010-12-06', $event['EventOccurrence'][0]['start_date'] );
        $this->assertEquals( null, $event['EventOccurrence'][0]['start_time'] );
        $this->assertEquals( null, $event['EventOccurrence'][0]['end_time'] );
        $this->assertEquals( '2010-12-09', $event['EventOccurrence'][0]['end_date'] );
        $this->assertEquals( '942696', $event['EventOccurrence'][0]['Poi']['vendor_poi_id'] );
        $this->assertEquals( '7773272-2010-12-06-3', $event['EventOccurrence'][0]['vendor_event_occurrence_id'] );

    }

    private function _dummyPoi()
    {
        $xmlFileName = $this->params['curl']['src']; // fetch the filename from Params
        $xml = simplexml_load_file( $xmlFileName );

        $venues = $xml->xpath('//occurrence/venue_id');
        $processedVenues = array();
        foreach( $venues as $venue )
        {
            if( in_array( (string) $venue, $processedVenues ) )
            {
                continue; // add Venue only once
            }

            $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => $this->vendor['id'], 'vendor_poi_id' => (string) $venue ) );
            $processedVenues[] = (string) $venue;
        }
    }
}