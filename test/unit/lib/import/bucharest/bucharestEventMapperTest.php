<?php
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
//        $this->assertEquals( 0, $actual);

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
            
            ProjectN_Test_Unit_Factory::add( 'Poi', array( 'Vendor' => $this->vendor, 'vendor_poi_id' => (string) $venue ) );
            $processedVenues[] = (string) $venue;
        }
    }
}