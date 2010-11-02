<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';
/**
 * Test for Chicago Event Mapper
 *
 * @package test
 * @subpackage chicago.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0 
 *
 *
 */
class ChicagoFeedEventMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'chicago' );

        $this->params =  array( 'split' => array( 'index' => 1, 'chunk' => 3 ), 'ftp' => array( 'classname' => 'FTPClientMock', 'ftp' => 'ftp.timeoutchicago.com', 'username' => 'test', 'password' => 'test', 'dir' => '/', 'file' => TO_TEST_DATA_PATH.'/chicago/chicago_new_event_poi.short.tmp.xml' ) );

    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
        // Delete the TMP file
        if(file_exists( $this->params['ftp']['file']))
        {
            unlink($this->params['ftp']['file']);
        }
    }

    public function testMapEvents()
    {
        // Load XML and Data Mapper
        $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/chicago/chicago_new_event_poi.short.xml' );

        $xml = $this->setupPoisAndDates( $xml ); // Setup Dummy POIs and update DATEs

        // Save it as Temporary Files
        file_put_contents( $this->params['ftp']['file'], $xml->saveXML());

        $pois = Doctrine::getTable( 'Poi' )->findAll();
        $this->assertGreaterThan( 15, $pois->count(), 'There should be at-least 15 POIS');
        
        // Run First Half of the import
        $dataMapper = new ChicagoFeedEventMapper( $this->vendor, $this->params);

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        unset( $importer );

        // Get all events added
        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 5, $events->count(), 'First Hlaf should be Imported.');

        // Run Second import
        $this->params['split']['index'] = 2;
        $dataMapper = new ChicagoFeedEventMapper( $this->vendor, $this->params ) ;

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        unset( $importer );

        // Get all events added First + second
        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 10, $events->count(), 'First + second should add upto total count.');

        // Run Third import
        $this->params['split']['index'] = 3;
        $dataMapper = new ChicagoFeedEventMapper( $this->vendor, $this->params ) ;

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        unset( $importer );

        // Get all events added First + second
        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 15, $events->count(), 'First + second + Third should add upto total count.');

        // used for Occurrence checking
        $startDateFormatted = strtotime( '+2 day' );
         
        // Events
        $event = $events[0];

        $this->assertEquals( '270989', $event['vendor_event_id'], 'Vendor Event ID miss matching');
        $this->assertEquals( 'Dancing on the Deck', $event['name'], 'Event Name miss matching');
        $this->assertEquals( 'Set sail at this third annual cruise featuring a buffet, open bar, DJ, dancing, sexy sailor boys', mb_substr( $event['description'], 0, 96), 'Event Description miss matching');
        $this->assertEquals( '$150', $event['price'], 'Event Price miss matching');
        $this->assertEquals( 1, $event['VendorEventCategory']->count() , 'Category Count should be 1');
        
        $category = array_pop($event['VendorEventCategory']->toArray());
        $this->assertEquals( 'Gay & Lesbian | Events & meetings', $category['name'] , 'Category Count should be Gay & Lesbian | Events & meetings');

        // occurrences
        $this->assertEquals( 1, $event['EventOccurrence']->count(), 'Event occurrence Count Missmatch');

        // Vaidate occurrence Dynamic Date
        $occurrence = $event['EventOccurrence'][0];

        $this->assertEquals( '270989_100576_'.date('Ymd', strtotime( '+2 day' )).'120001' , $occurrence['vendor_event_occurrence_id'], 'Invalid Event Occurrence ID' );

        // Check POI category
        $poi = $occurrence['Poi'];
        $this->assertEquals( '100576', $poi['vendor_poi_id'], 'Mapped to wrong POI');
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Poi missing Category, Should be same as Event');
        $this->assertEquals( 'Gay & Lesbian | Events & meetings', $poi['VendorPoiCategory'][0]['name'], 'Invalid Poi Category, Should be same as Event');

        // Events
        $event = $events[2];
        $this->assertEquals( '270849', $event['vendor_event_id'], 'Vendor Event ID miss matching');
        $this->assertEquals( 'College of Complexes', $event['name'], 'Event Name miss matching');
        $this->assertEquals( 'This free-speech forum dubbed “The Playground for People Who Think” tackles', substr( $event['description'], 0, 79), 'Event Description miss matching');
        $this->assertEquals( '$3, plus $5 food and drink minimum', $event['price'], 'Event Price miss matching');
        $this->assertEquals( 1, $event['VendorEventCategory']->count() , 'Category Count should be 1');

        $category = array_pop($event['VendorEventCategory']->toArray());
        $this->assertEquals( 'Around Town | City Picks', $category['name'] , 'Category Count should be Around Town | City Picks');

        // occurrences
        $this->assertEquals( 1, $event['EventOccurrence']->count(), 'Event occurrence Count Missmatch');
        
        // Vaidate occurrence Dynamic Date
        $occurrence = $event['EventOccurrence'][0];

        $this->assertEquals( '270849_100237_'.date('Ymd', strtotime( '+2 day' )).'120001' , $occurrence['vendor_event_occurrence_id'], 'Invalid Event Occurrence ID' );

        // Check POI category
        $poi = $occurrence['Poi'];
        $this->assertEquals( '100237', $poi['vendor_poi_id'], 'Mapped to wrong POI');
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Poi missing Category, Should be same as Event');
        $this->assertEquals( 'Around Town | City Picks', $poi['VendorPoiCategory'][0]['name'], 'Invalid Poi Category, Should be same as Event');

        
    }

    /**
     * Test for Spliting as 2 Chunks, this will have left over and split should handle this and import all 15 events
     */
    public function testMapEvents2Chunk()
    {
        // Load XML and Data Mapper
        $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/chicago/chicago_new_event_poi.short.xml' );

        $xml = $this->setupPoisAndDates( $xml ); // Setup Dummy POIs and update DATEs

        // Save it as Temporary Files
        file_put_contents( $this->params['ftp']['file'], $xml->saveXML());

        // Run First Half of the import
        $this->params['split']['chunk'] = 2;
        $this->params['split']['index'] = 1;
        $dataMapper = new ChicagoFeedEventMapper( $this->vendor, $this->params);

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        unset( $importer );

        // Get all events added
        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 8, $events->count(), 'First Hlaf should be Imported.');

        // Run Second import
        $this->params['split']['index'] = 2;
        $dataMapper = new ChicagoFeedEventMapper( $this->vendor, $this->params ) ;

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        unset( $importer );

        // Get all events added First + second
        $events = Doctrine::getTable( 'Event' )->findAll();
        $this->assertEquals( 15, $events->count(), 'First + second should add upto total count.');


    }
    /**
   * Setup Dummy Pois based on venue ID's and Change XMl Feed Dates
   * @param SimpleXMLElement $xml
   * @return SimpleXMLElement
   */
    private function setupPoisAndDates( SimpleXMLElement $xml )
    {
        // Setup POIS
        $venueIDs = $xml->xpath('/body/event/date/venue/address_id');

        foreach( $venueIDs as $venue )
        {
          // Set POI's
          $poi = ProjectN_Test_Unit_Factory::get( 'Poi' , array( 'vendor_poi_id' => (string) $venue, 'vendor_id' => $this->vendor['id'], 'poi_name' => 'dummy' ) );
          $poi->save();
          $poi['VendorPoiCategory']->delete(); // Delete the Default pre-save category
        }

        // Setup Dynamix dates
        for( $i = 0 ; $i < count( $xml->event) ; $i++ )
        {
          $xml->event[$i]->date_end = date('Y-m-d H:i:s' , strtotime( '+1 week' ) );

          for( $x = 0 ; $x < count($xml->event[$i]->date) ; $x++)
          {
              $xml->event[$i]->date[$x]->start = date('Y-m-d' , strtotime( '+2 day' ) ) . '12:00:01';
          }
        }

        return $xml;
    }
}

?>
