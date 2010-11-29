<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

/**
 * Test for Chicago BC Mapper
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
class ChicagoFeedBCMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    private $params;
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'chicago' );

        $this->params =  array(  'type' => 'bc','ftp' => array( 'classname' => 'FTPClientMock', 'ftp' => 'ftp.timeoutchicago.com', 'username' => 'test', 'password' => 'test', 'dir' => '/', 'file' => TO_TEST_DATA_PATH.'/chicago/short_toc_bc.xml' ) );
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapBC()
    {
        // Load XML and Data Mapper
        $dataMapper = new ChicagoFeedBCMapper( $this->vendor, $this->params );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();

        $pois = Doctrine::getTable( 'Poi' )->findAll();

        // One of the ED is closed, so there should be 5 Pois added to DB
        $this->assertEquals( 5, $pois->count(), 'Should be 5 Pois as 1 Closed');

        $poi = $pois[0]; // test the First POI

        $this->assertEquals( '1003', $poi['vendor_poi_id'], 'Vendor Poi ID missmatch');
        $this->assertEquals( 'The Store', $poi['poi_name'], 'Poi Name missmatch');
        $this->assertEquals( 'A dark room. A long wooden bar. A couple of middle-aged guys playing video', substr( $poi['description'], 0, 74 ), 'Description missmatch');

        $this->assertEquals( '2002 N Halsted St', $poi['street'], 'Street missmatch');
        $this->assertEquals( '60614', $poi['zips'], 'Zip missmatch');
        $this->assertEquals( 'between Armitage and Dickens Aves', $poi['additional_address_details'], 'Additional Address missmatch');
        $this->assertEquals( 'El: Brown, Purple (rush hrs) to Armitage. Bus: 8, 11, 73', $poi['public_transport_links'], 'Public Transport links missmatch');
        $this->assertEquals( '+1 773 327 7766', $poi['phone'], 'Wrong phone number');

        // Property test
        $this->assertEquals( 1, $poi['PoiProperty']->count(), 'Should be only 1 Property');
        $this->assertEquals( 'features', $poi['PoiProperty'][0]['lookup'], 'Lookup should be feature' );
        $this->assertEquals( 'No stairs to bathroom, Wheelchair-accessible bathroom, Late Night, Private party room(s), Video/pinball games', $poi['PoiProperty'][0]['value'], 'Value should be comma seperated' );

        // category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Should be only 1 Category');
        $this->assertEquals( 'Pick-up joints', $poi['VendorPoiCategory'][0]['name'], 'Category name should be Pick-up joints');

        // Check for multiple line category poi
        $poi = $pois[2];
        $this->assertEquals( '1617', $poi['vendor_poi_id'], 'Vendor Poi ID missmatch');
        $this->assertEquals( 'The Boss Bar', $poi['poi_name'], 'Poi Name missmatch');
        $this->assertEquals( 'Sun–Fri 11am–4am, Sat 11am–5am', $poi['openingtimes'], 'Opening hours missmatch');

        // check Category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Should be only 1 Category');
        $this->assertEquals( 'Pick-up joints | New line', $poi['VendorPoiCategory'][0]['name'], 'Category name should be Pick-up joints | New line');

    }
}

?>
