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

        $events = Doctrine::getTable( 'Event' )->findAll();

        $event = $events[0];
        $this->assertEquals( 'We back, we go HARD', $event['name']);
        $this->assertStringStartsWith('新锐派对组织SuperColab将携东京最著名的前卫DJ团', $event['description']);
        $this->assertStringEndsWith('DJ团“HARD”来到北...', $event['short_description']);
        $this->assertEquals('门票50元', $event['price']);

        // Check Category
        $this->assertEquals('1', $event['VendorEventCategory']->count() );
        $categories = $event['VendorEventCategory']->toArray();
        $category = array_shift( $categories );
        $this->assertEquals('北京 | 派对 | 双周推荐榜', $category['name'] );


        // Check for occurrences
        $this->assertEquals( 1 , $event['EventOccurrence'] ->count() );
        $occurrence = $event['EventOccurrence'][0];

        $this->assertEquals( '2010-11-13' , $occurrence['start_date'] );
        $this->assertEquals( '22:00:00' , $occurrence['start_time'] );
        $this->assertEquals( '2010-11-13' , $occurrence['end_date'] );
        $this->assertEquals( '4' , $occurrence['poi_id'] );


        // Test another Event
        $event = $events[2];
        $this->assertEquals( '《收获荒地》、《中国人来了》、《省长先生》', $event['name']);
        $this->assertEquals('', $event['description']);
        $this->assertEquals('', $event['short_description']);
        $this->assertEquals('', $event['price']);

        // Check Category
        $this->assertEquals('1', $event['VendorEventCategory']->count() );
        $categories = $event['VendorEventCategory']->toArray();
        $category = array_shift( $categories );
        $this->assertEquals('北京 | 影视 | 独立放映', $category['name'] );


        // Check for occurrences
        $this->assertEquals( 5 , $event['EventOccurrence'] ->count() );
        $occurrence = $event['EventOccurrence'][0];

        $this->assertEquals( '2010-11-13' , $occurrence['start_date'] );
        $this->assertEquals( null , $occurrence['start_time'] );
        $this->assertEquals( '2010-11-13' , $occurrence['end_date'] );
        $this->assertEquals( '2' , $occurrence['poi_id'] );
    }

    /**
     * Add Dummy Poi for Event occurrences to be added in DB
     */
    private function _addDynamicPoi()
    {
        for( $i = 1; $i <= 4; $i++ )
        {
            ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_id' =>  29, 'vendor_poi_id' => $i) ); // Beijing ZH
        }
    }
}

class ChinaFeedEventsMapperMock extends ChinaFeedEventsMapper
{
    protected function  _loadXML() {
        $this->xmlNodes = simplexml_load_file( $this->params['datasource']['src'] );
    }

    
}