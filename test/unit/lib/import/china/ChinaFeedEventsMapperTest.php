<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of China Movie Mapper
 *
 * @package test
 * @subpackage china.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class ChinaFeedEventsMapperTest extends PHPUnit_Framework_TestCase
{
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        Doctrine::loadData('data/fixtures');
        $this->_addDynamicPoi();
        
        $params = array( 'datasource' => array( 'classname' => 'FormScraper', 'src' => TO_TEST_DATA_PATH . '/china/Events.xml', 'username' => 'test', 'password' => 'test', 'xmlsrc' => 'test' ) );
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'beijing_zh' );

        $importer = new Importer( );
        $importer->addDataMapper( new ChinaFeedEventsMapperMock($vendor, $params) );
        $importer->run();
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }


    public function testMapEvent()
    {
        $this->assertEquals( 4, Doctrine::getTable( 'Poi' )->count() );
        $this->assertEquals( 3, Doctrine::getTable( 'Event' )->count() );

    }

    /**
     * Add Dummy Poi for Event occurrences to be added in DB
     */
    private function _addDynamicPoi()
    {
        for( $i = 1; $i <= 4; $i++ )
        {
            ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' =>  29) ); // Beijing ZH
        }
    }
}

class ChinaFeedEventsMapperMock extends ChinaFeedEventsMapper
{
    protected function  _loadXML() {
        $this->xmlNodes = simplexml_load_file( $this->params['datasource']['src'] );
    }

    
}