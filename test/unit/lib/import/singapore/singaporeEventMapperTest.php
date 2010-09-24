<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test for Singapore Poi Mapper
 *
 * @package test
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class singaporeEventMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Store Temporary File name
     * @var string
     */
    private $tmpFile;
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        // Setup Tmp File
        $this->tmpFile  = TO_TEST_DATA_PATH . '/singapore/new_events_list_tmp.xml';
        $this->createTmpFile();
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();

        // Remove the Files
        if(file_exists( $this->tmpFile ) )
        {
            unlink ( $this->tmpFile );
        }
    }

    public function testMapEvent()
    {
        // Get the XML
        $dataSource = new singaporeDataSource( 'event', $this->tmpFile, 'CurlMock' );
        $xml = $this->setDynamicDateTime( $dataSource->getXML() );

        // create Data Mapper
        $dataMapper = new singaporeEventMapper( $xml );
        $this->createDummyPoi( $xml );

        // Check POI count
        $pois   =  Doctrine::getTable( 'Poi' )->findAll();
        $this->assertEquals( 3, $pois->count(), 'Should have 3 POIs' );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();

        // get all events
        $events = Doctrine::getTable( 'Event' )->findAll();

        $this->assertEquals( 3, $events->count(), 'There should be 3 Events' );

        $event = $events[0];

        $this->assertEquals( '9765', $event['vendor_event_id']);
        $this->assertEquals( 'Manga Drawing', $event['name']);
        $this->assertEquals( 'An interactive workshop for hardcore anime and manga fans. The course covers basic drawing', substr($event['description'],0,90) );
        $this->assertEquals( '', $event['price']);

        // Category
        $this->assertEquals( 1, $event['VendorEventCategory']->count());
        $this->assertEquals( 'Books | Events', $event['VendorEventCategory']['Books | Events']['name']);

        // occurrences
        $this->assertEquals( 2, $event['EventOccurrence']->count());
        $oc = $event['EventOccurrence'][0];
        $this->assertEquals( date('Y-m-d', strtotime(' +1 Day') ), $oc['start_date']);
        $this->assertEquals( date('Y-m-d', strtotime(' +2 Day') ), $oc['end_date']);

        $oc = $event['EventOccurrence'][1];
        $this->assertEquals( date('Y-m-d', strtotime(' +3 Day') ), $oc['start_date']);
        $this->assertNull($oc['end_date']);
        // Check the Dates


        // Check 2nd event
        $event = $events[1];

        $this->assertEquals( '9764', $event['vendor_event_id']);
        $this->assertEquals( 'Yellow River Cantata', $event['name']);
        $this->assertEquals( 'between $10.00 and $100.00', $event['price']);
        
        // Category
        $this->assertEquals( 1, $event['VendorEventCategory']->count());
        $this->assertEquals( 'Music | Opera and Vocal', $event['VendorEventCategory']['Music | Opera and Vocal']['name']);

        // occurrences
        $this->assertEquals( 6, $event['EventOccurrence']->count());
        $oc = $event['EventOccurrence'][0];
        $this->assertEquals( date('Y-m-d', strtotime(' +1 Day') ), $oc['start_date']);
        $this->assertEquals( date('Y-m-d', strtotime(' +1 Day') ), $oc['end_date']);

        $oc = $event['EventOccurrence'][1];
        $this->assertEquals( date('Y-m-d', strtotime(' +2 Day') ), $oc['start_date']);
        $this->assertNull( $oc['end_date']);
        $oc = $event['EventOccurrence'][2];
        $this->assertEquals( date('Y-m-d', strtotime(' +3 Day') ), $oc['start_date']);
        $this->assertNull( $oc['end_date']);
        $oc = $event['EventOccurrence'][3];
        $this->assertEquals( date('Y-m-d', strtotime(' +4 Day') ), $oc['start_date']);
        $this->assertNull( $oc['end_date']);
        $oc = $event['EventOccurrence'][4];
        $this->assertEquals( date('Y-m-d', strtotime(' +8 Day') ), $oc['start_date']);
        $this->assertNull( $oc['end_date']);
    }

    /**
     * update 1st and 2nd Event occurrence Date time to Valid once
     * @param SimpleXMLElement $eventNodes
     * @return SimpleXMLElement
     */
    private function setDynamicDateTime(SimpleXMLElement $eventNodes )
    {
        $eventNodes->event[0]->date_start = date('D, d M Y H:i:s +0000', strtotime( '+1 Day' ));
        $eventNodes->event[0]->date_end = date('D, d M Y H:i:s +0000', strtotime( '+2 Day' ));
        $eventNodes->event[0]->alternative_dates = date('m/d/Y', strtotime( '+3 Day' ));

        $eventNodes->event[1]->date_start = date('D, d M Y H:i:s +0000', strtotime( '+1 Day' ));
        $eventNodes->event[1]->date_end = date('D, d M Y H:i:s +0000', strtotime( '+1 Day' ));
        $eventNodes->event[1]->alternative_dates = date('m/d/Y', strtotime( '+2 Day' )).' - '.date('m/d/Y', strtotime( '+4 Day' )) . PHP_EOL . date('m/d/Y', strtotime( '+8 Day' )).' - '.date('m/d/Y', strtotime( '+9 Day' ));

        $eventNodes->event[2]->date_start = date('D, d M Y H:i:s +0000', strtotime( '+2 Day' ));
        $eventNodes->event[2]->date_end = date('D, d M Y H:i:s +0000', strtotime( '+2 Day' ));
        return $eventNodes;
    }

    private function createDummyPoi( SimpleXMLElement $eventNodes )
    {
        foreach( $eventNodes->event as $eventNode )
        {
            if( !isset( $eventNode->venue ) )
            {
                continue;
            }
            $venue = $eventNode->venue;
            $poi = ProjectN_Test_Unit_Factory::get( 'Poi' , array( 'vendor_poi_id' => (string) $venue->id, 'vendor_id' => 3, 'poi_name' => (string)$venue->name ) );
            $poi->save();
        }
    }
    
    /**
     * Create a temporary Files with updated Path in Link
     */
    private function createTmpFile()
    {
        if(file_exists( $this->tmpFile ) )
        {
            unlink ( $this->tmpFile );
        }

        // create new File
        $fileData   = file_get_contents( TO_TEST_DATA_PATH . '/singapore/new_events_list.xml' );

        // Update Links
        $xml        = simplexml_load_string( $fileData );

        // update Path
        for( $i = 0; $i < 3; $i++ )
        {
            $xml->channel->item[$i]->link    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->link;
            $xml->channel->item[$i]->guid    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->guid;
        }

        
        file_put_contents( $this->tmpFile, $xml->saveXML() );
    }
}

?>
