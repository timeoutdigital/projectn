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

    public function testGetXML()
    {
        $dataSource = new singaporeDataSource( 'poi', $this->tmpFile, 'CurlMock' );
        $xml = $dataSource->getXML();

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
