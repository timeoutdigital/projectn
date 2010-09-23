<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test for Singapore Data Source
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
class singaporeDataSourceTest extends PHPUnit_Framework_TestCase
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

    public function testFetchXML()
    {
        // test #666
        $this->createPubDatePoi(); // This will make the last & live url to skip!
        $dataSource = new singaporeDataSource( 'poi', $this->tmpFile, 'CurlMock' );
        $xml = $dataSource->getXML();

        // Existing poi's matching Pubished Date should be skipped #666
        $this->assertEquals( 2, count($xml), 'Venue Should have two XML nodes'); // Should have 2 XML Nodes
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
        $fileData   = file_get_contents( TO_TEST_DATA_PATH . '/singapore/new_venue_list_old_pubdate.xml' );

        // Update Links
        $xml        = simplexml_load_string( $fileData );

        for( $i = 0; $i < 2; $i++ ) // #666 - Third one is LIve URl which should never be requested
        {
            $xml->channel->item[$i]->link    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->link;
            $xml->channel->item[$i]->guid    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->guid;
        }

        file_put_contents( $this->tmpFile, $xml->saveXML() );
    }

    /**
     * This will Create a POI that has the PUB date exactly same as in XML feed
     * That should not be loaded in XMl feed when SingaporeDataSource is called
     */
    private function createPubDatePoi()
    {
        $poi = new Poi();
        $poi['vendor_poi_id'] = '666';
        $poi['poi_name'] = 'Test Poi Name';
        $poi['street'] = 'Test street';
        $poi['city'] = 'Singapore';
        $poi['local_language'] = 'en-US';
        $poi['country'] = 'SGP';
        $poi['vendor_id'] = 3;
        $poi['review_date'] = date("Y-m-d H:i:s" , strtotime( 'Thu, 26 Aug 10 07:10:01 +0000' ) );

        $poi->save();
    }
    
}
