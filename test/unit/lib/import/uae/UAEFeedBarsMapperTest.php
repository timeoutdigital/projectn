<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for UAE Feed Bars Mapper
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

class UAEFeedBarsMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );

        $importer = new Importer();
        $importer->addDataMapper( new UAEFeedBarsMapper( $this->vendor, $this->_getParams() ) );
        $importer->run();
    }

    private function _getParams()
    {
        return array(
            'type' => 'bar',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/uae/dubai_bars.xml'
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
        $this->assertEquals( 3, $pois->count() );

        $poi = $pois[0];
        $this->assertEquals( '17665', $poi['vendor_poi_id']);
        $this->assertEquals( 'Rosso', $poi['poi_name']);
        $this->assertEquals( Null, $poi['street']);
        $this->assertEquals( 'The Walk, Jumeirah Beach Residence, Dubai Marina, Dubai', $poi['additional_address_details']);
        $this->assertEquals( '+971 4 428 3088', $poi['phone']);
        $this->assertEquals( 'Open daily 7.30pm-midnight', $poi['openingtimes']);
        $this->assertEquals( 'The Walk, Jumeirah Beach Residence, Dubai Marina, Dubai',  $poi['geocode_look_up'] );

        // assert property
        $this->assertEquals( 1 , $poi['PoiProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/bars/reviews/17665-rosso', $poi['PoiProperty'][0]['value']);

        $this->assertEquals( null, $poi['latitude']);
        $this->assertEquals( null, $poi['longitude']);

        $poi = $pois[1];
        $this->assertEquals( '17526', $poi['vendor_poi_id']);
        $this->assertEquals( 'Krossroads Bar & Lounge', $poi['poi_name']);
        $this->assertEquals( 'Good for obscure fun, this hidden gem boasts unusual aesthetics', $poi['description']);
        $this->assertEquals( 'Khalid Bin Walid Street', $poi['street']);
        $this->assertEquals( 'Bur Dubai', $poi['district']);
        $this->assertEquals( 'Dubai', $poi['city']);
        $this->assertEquals( null, $poi['additional_address_details']);
        $this->assertEquals( '+971 5 0624 7469', $poi['phone']);
        $this->assertEquals( 'Open daily noon-3am', $poi['openingtimes']);
        $this->assertEquals( 'http://www.krossroadsdubai.com', $poi['url']);
        $this->assertEquals( 'enquiries@krossroadsdubai.com', $poi['email']);
        $this->assertEquals( 'Khalid Bin Walid Street, Bur Dubai, Dubai',  $poi['geocode_look_up'] );

        // Lat / Long
        $this->assertEquals( '25.256962', $poi['latitude']);
        $this->assertEquals( '55.293503', $poi['longitude']);

        // assert property
        $this->assertEquals( 1 , $poi['PoiProperty']->count() );
        $this->assertEquals( 'http://www.timeoutdubai.com/bars/reviews/17526-krossroads-bar-a-lounge', $poi['PoiProperty'][0]['value']);

        $this->assertEquals( 'Bars', $poi['VendorPoiCategory'][0]['name']); // category
        
    }
}