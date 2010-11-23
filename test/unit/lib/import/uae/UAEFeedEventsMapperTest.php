<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for UAE Feed Events Mapper
 *
 * @package test
 * @subpackage uae.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.1.0
 *
 */

class UAEFeedEventsMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );
        
        $importer = new Importer();
        $mapper = new UAEFeedEventsMapper( $this->vendor, $this->_getParams() );
        $mapper->xml = $this->dynamicDates( $mapper->xml );
        $importer->addDataMapper( $mapper );
        $importer->run();
    }

    private function _getParams()
    {
        return array(
            'type' => 'bar',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/uae/dubai_latest_events-venue.xml',
                'xslt' => 'uae_events.xml'
             )
        );
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapEvents()
    {
        $pois = Doctrine::getTable( 'Poi' )->findAll( );
        $this->assertEquals( 7, $pois->count() );

        $events = Doctrine::getTable( 'Event' )->findAll( );
        $this->assertEquals( 4, $events->count() );

        // assert Event
        $event = $events[0];
        $this->assertEquals( '7753', $event['vendor_event_id']);
        $this->assertEquals( 'Dubai Dolphinarium - 11am show (Thu)', $event['name']);
        $this->assertEquals( 'Have you ever dreamed of seeing live dolphins and seals? Now your dream can', mb_substr( $event['description'], 0, 75 ) );
        $this->assertEquals( 'Adult - Dhs120(VIP), Child - Dhs80(VIP), Adult - Dhs100(Regular), Child - Dhs50(Regular)', $event['price']);

        $this->assertEquals( 3, $event['EventProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/nightlife/events/7753-dubai-dolphinarium-11am-show-thu', $event['EventProperty'][0]['value']);
        $this->assertEquals( '+971 4 336 9773', $event['EventProperty'][1]['value']);
        $this->assertEquals( 'smo@dubaidolphinarium.ae', $event['EventProperty'][2]['value']);

        // check Occurrences
        $this->assertEquals( 1, $event['EventOccurrence']->count() );
        $oc = $event['EventOccurrence'][0];

        $this->assertEquals( date( 'Y-m-d', strtotime( '+5 day' ) ), $oc['start_date']);

        // Another validation
        $event = $events[2];
        $this->assertEquals( '7754', $event['vendor_event_id']);
        $this->assertEquals( 'Dubai Dolphinarium - 3pm show (Fri)', $event['name']);
        $this->assertEquals( 'Have you ever dreamed of seeing live dolphins and seals? Now your dream can', mb_substr( $event['description'], 0, 75 ) );
        $this->assertEquals( 'Adult - Dhs120(VIP), Child - Dhs80(VIP), Adult - Dhs100(Regular), Child - Dhs50(Regular)', $event['price']);

        $this->assertEquals( 2, $event['EventProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/nightlife/events/7754-dubai-dolphinarium-3pm-show-fri', $event['EventProperty'][0]['value']);
        $this->assertEquals( 'smo@dubaidolphinarium.ae', $event['EventProperty'][1]['value']);

        // check Occurrences
        $this->assertEquals( 2, $event['EventOccurrence']->count() );
        $oc = $event['EventOccurrence'][0];
        $this->assertEquals( date( 'Y-m-d', strtotime( '+2 day' ) ), $oc['start_date']);
        $oc = $event['EventOccurrence'][1];
        $this->assertEquals( date( 'Y-m-d', strtotime( '+3 day' ) ), $oc['start_date']);

    }

    /**
     * update Event occurrence date to Dynamic Dates
     * @param SimpleXMLElement $xml
     */
    private function dynamicDates( SimpleXMLElement $xml )
    {
        $xml->event[0]->{'day-occurences'}->{'day-occurence'}[0]->start_date = date( 'Y-m-d', strtotime( '+5 day' ) );

        $xml->event[2]->{'day-occurences'}->{'day-occurence'}[0]->start_date = date( 'Y-m-d', strtotime( '+2 day' ) );
        $xml->event[2]->{'day-occurences'}->{'day-occurence'}[1]->start_date = date( 'Y-m-d', strtotime( '+3 day' ) );

        foreach( $xml->event as $event )
        {
            foreach( $event->{'day-occurences'}->{'day-occurence'} as $occurrence )
            {
                $poiID = (string )$occurrence->{'venue_id'};

                $poi = ProjectN_Test_Unit_Factory::get( 'Poi', array( 'vendor_poi_id' => $poiID, 'poi_name' => 'Dummy Poi', 'vendor_id' => $this->vendor['id'] ) );
                $poi->save();
            }
        }

        return $xml;
    }
}