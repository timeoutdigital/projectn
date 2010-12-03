<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Beirut Event Mapper test
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

class BeirutFeedEventMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    private $params;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        // Set up vendor and params
        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'beirut' );
        $this->params = array( 'type' => 'event', 'curl' => array(
                                                                'classname' => 'CurlMock',
                                                                'src' => TO_TEST_DATA_PATH . '/beirut/event.xml',
                                                                ) );

        // add dummy POI
        $this->_addDummyPoi();
        // Import Data
        $importer = new Importer( );
        $importer->addDataMapper( new BeirutFeedEventMapper( $this->vendor, $this->params ) );
        $importer->run();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapEvent()
    {
        $this->assertEquals( 4 , Doctrine::getTable( 'Poi' )->count() );
        $this->assertEquals( 6, Doctrine::getTable( 'Event' )->count() );

        $events = Doctrine::getTable( 'Event' )->findAll();

        $event = $events[0];
        $this->assertEquals( '3909', $event['vendor_event_id']);
        $this->assertEquals( 'LoveDough at Brut', $event['name']);
        $this->assertEquals( "Lovedough started off in early 2000, with a mission to bring back the good ol' non-commercial hip hop culture. Since their beginning, Lovedough' shows sold out in 35 different venues internationally such as London, Brighton, Manchester, Leeds, Newcastle, Cork, Ibiza, Mallorca, Abu Dhabi, Damascus, Las Vegas, Hollywood and Beirut to name a few. Beirut's newest host for Lovedough' music movement is Brut.For more information and reservations call 03 030352", $event['description']);
        $this->assertEquals( "The international RnB & hip hop sensation", $event['short_description']);
        $this->assertEquals( null, $event['url']);
        $this->assertEquals( null, $event['booking_url']);
        $this->assertEquals( null, $event['price']);
        $this->assertEquals( null, $event['rating']);
        $this->assertEquals( null, $event['review_date']);

        $this->assertEquals( 1, $event['EventOccurrence']->count() );
        $this->assertEquals( "2010-12-03" , $event['EventOccurrence'][0]['start_date'] );
        $this->assertEquals( "2010-12-03" , $event['EventOccurrence'][0]['end_date'] );
        $this->assertEquals( null , $event['EventOccurrence'][0]['start_time'] );
        $this->assertEquals( null , $event['EventOccurrence'][0]['end_time'] );
        $this->assertEquals( "4" , $event['EventOccurrence'][0]['poi_id'] );
        $this->assertEquals( "3909-2010-12-03-4" , $event['EventOccurrence'][0]['vendor_event_occurrence_id'] );
        
        
    }

    private function _addDummyPoi()
    {
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 39, 'vendor_poi_id' => 40 ));
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 39, 'vendor_poi_id' => 1854 ));
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 39, 'vendor_poi_id' => 858 ));
        ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' => 39, 'vendor_poi_id' => 1195 ));
    }
}