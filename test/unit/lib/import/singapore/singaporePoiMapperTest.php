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
class singaporePoiMapperTest extends PHPUnit_Framework_TestCase
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
        $this->tmpFile  = TO_TEST_DATA_PATH . '/singapore/new_venue_list_tmp.xml';
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

    public function testMapPoi()
    {
        // Get the XML
        $dataSource = new singaporeDataSource( 'poi', 'CurlMock', $this->tmpFile );
        $xml = $dataSource->getXML();

        // create Data Mapper
        $dataMapper = new singaporePoiMapper( $xml );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();

        // Get all POIS
        $pois = Doctrine::getTable( 'Poi' )->findAll();

        $this->assertEquals( 2, $pois->count(), 'Should have 2 Pois, same as XML');

        $poi = $pois[0];

        // assert Details
        $this->assertEquals( '2524', $poi['vendor_poi_id']);
        $this->assertEquals( 'Singapore Repertory Theatre', $poi['poi_name']);
        $this->assertEquals( '11 Unity Street', $poi['street']);
        $this->assertEquals( '237995', $poi['zips']);
        $this->assertEquals( 'http://www.srt.com.sg/', $poi['url']);
        $this->assertEquals( '+65 6 221 5585', $poi['phone']);
        $this->assertEquals( 'Near station: Clarke Quay, Buses: 64, 123, 143', $poi['public_transport_links']);

        $this->assertEquals( 'This is the Singapore Repertory Theatre office, but most', mb_substr( $poi['description'], 0, 56) );

        // Check Category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count());
        $this->assertEquals( 'Performance | Theatre', $poi['VendorPoiCategory'][0]['name']);

        // Property
        $this->assertEquals( 1, $poi['PoiProperty']->count());

        $this->assertEquals( 'Timeout_link', $poi['PoiProperty'][0]['lookup']);
        $this->assertEquals( 'http://www.timeoutsingapore.com/performance/venues/theatre/singapore-repertory-theatre', $poi['PoiProperty'][0]['value']);

        // Test Poi 2
        $poi = $pois[1];

        $this->assertEquals( '2523', $poi['vendor_poi_id']);
        $this->assertEquals( 'Beer Market', $poi['poi_name']);
        $this->assertEquals( 'Block 3B River Valley Road', $poi['street']);
        $this->assertEquals( '179021', $poi['zips']);
        $this->assertEquals( 'http://www.beermarket.com.sg', $poi['url']);
        $this->assertEquals( '+65 9 661 8283', $poi['phone']);
        $this->assertEquals( 'Near station: Clarke Quay', $poi['public_transport_links']);
        $this->assertEquals( 'Clarke Quay - The Foundry #01-17/02-02', $poi['additional_address_details']);
        
        $this->assertEquals( '\'Buy low, drink high\', cautions the owners of this stock market-inspired', mb_substr( $poi['description'], 0, 72) );

        // Check Category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count());
        $this->assertEquals( 'Clubs | Bars', $poi['VendorPoiCategory'][0]['name']);

        // Property
        $this->assertEquals( 2, $poi['PoiProperty']->count());
        $this->assertEquals( 'Critics_choice', $poi['PoiProperty'][0]['lookup']);
        $this->assertEquals( 'Y', $poi['PoiProperty'][0]['value']);

        $this->assertEquals( 'Timeout_link', $poi['PoiProperty'][1]['lookup']);
        $this->assertEquals( 'http://www.timeoutsingapore.com/clubs/venues/bars/beer-market', $poi['PoiProperty'][1]['value']);

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
        $fileData   = file_get_contents( TO_TEST_DATA_PATH . '/singapore/new_venue_list.xml' );

        // Update Links
        $xml        = simplexml_load_string( $fileData );

        $xml->channel->item[0]->link    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[0]->link;
        $xml->channel->item[0]->guid    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[0]->guid;

        $xml->channel->item[1]->link    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[1]->link;
        $xml->channel->item[1]->guid    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[1]->guid;

        file_put_contents( $this->tmpFile, $xml->saveXML() );
    }
    
}
?>
