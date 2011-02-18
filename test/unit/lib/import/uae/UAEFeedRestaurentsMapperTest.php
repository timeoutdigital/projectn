<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for UAE Resteraunt Mapper
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

class UAEFeedRestaurantsMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );

        $importer = new Importer();
        $importer->addDataMapper( new UAEFeedRestaurantsMapper( $this->vendor, $this->_getParams() ) );
        $importer->run();
    }

    private function _getParams()
    {
        return array(
            'type' => 'restaurant',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/uae/dubai_restaurents.xml',
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

    public function testMapBars()
    {
        // validate
        $pois = Doctrine::getTable( 'Poi' )->findAll();
        $this->assertEquals( 5, $pois->count() );

        $poi = $pois[0];
        $this->assertEquals( '17750', $poi['vendor_poi_id']);
        $this->assertEquals( 'Vantage', $poi['poi_name']);
        $this->assertEquals( 'Sheikh Zayed Road', $poi['street']);
        $this->assertEquals( '+971 4 377 2000', $poi['phone']);
        $this->assertEquals( 'Open daily 4pm-2pm', $poi['openingtimes']);

        // assert property
        $this->assertEquals( 2 , $poi['PoiProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/restaurants/reviews/17750-vantage', $poi['PoiProperty'][0]['value']);

        // #929 - Since the Unification of import task, we lost teh ability to Mock geocoders,
        // hence this test is Invalid! 
        // $this->assertEquals( null, $poi['latitude']);
        // $this->assertEquals( null, $poi['longitude']);

        $this->assertEquals( 'Eating & Drinking', $poi['VendorPoiCategory'][0]['name']); // category

        $poi = $pois[4];
        $this->assertEquals( '16391', $poi['vendor_poi_id']);
        $this->assertEquals( 'Eat & Drink', $poi['poi_name']);
        $this->assertEquals( 'Next to Choithram Supermarket', $poi['street']); // This is a problem!
        $this->assertEquals( '+971 4 394 3878', $poi['phone']);
        $this->assertEquals( 'Open daily 8am-2am', $poi['openingtimes']);

        // assert property
        $this->assertEquals( 3 , $poi['PoiProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/restaurants/reviews/16391-eat-a-drink', $poi['PoiProperty'][0]['value']);
        $this->assertEquals( 'Indian, Vegetarian', $poi['PoiProperty'][2]['value']);
        $this->assertEquals( '$16-24', $poi['PoiProperty'][1]['value']);

        $this->assertEquals( 'Eating & Drinking', $poi['VendorPoiCategory'][0]['name']); // category

    }
}