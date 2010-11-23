<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

/**
 * Test for Chicago ED Mapper
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
class ChicagoFeedEDMapperTest extends PHPUnit_Framework_TestCase
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

        $this->params =  array(  'type' => 'ed','ftp' => array( 'classname' => 'FTPClientMock', 'ftp' => 'ftp.timeoutchicago.com', 'username' => 'test', 'password' => 'test', 'dir' => '/', 'file' => TO_TEST_DATA_PATH.'/chicago/short_toc_ed.xml' ) );
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
        $dataMapper = new ChicagoFeedEDMapper( $this->vendor, $this->params );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();
        
        $importer->run();

        $pois = Doctrine::getTable( 'Poi' )->findAll();

        // One of the ED is closed, so there should be 5 Pois added to DB
        $this->assertEquals( 7, $pois->count(), 'Should be 5 Pois as 1 Closed');

        
        $poi = $pois[0]; // test the First POI

        $this->assertEquals( '1004', $poi['vendor_poi_id'], 'Vendor Poi ID missmatch');
        $this->assertEquals( 'A La Turka café and façade', $poi['poi_name'], 'Poi Name missmatch');
        $this->assertEquals( 'Modern-day Turkey isn’t the idea here, so if you’re looking for contempo', mb_substr( $poi['description'], 0, 72 ), 'Description missmatch');

        $this->assertEquals( '3134 N Lincoln Ave', $poi['street'], 'Street missmatch');
        $this->assertEquals( '60657', $poi['zips'], 'Zip missmatch');
        $this->assertEquals( 'between Barry and Belmont Aves', $poi['additional_address_details'], 'Additional Address missmatch');
        $this->assertEquals( 'El: Brown to Paulina. Bus: 9, 11, 77', $poi['public_transport_links'], 'Public Transport links missmatch');
        $this->assertEquals( '+1 773 935 6101', $poi['phone'], 'Wrong phone number');

        // Property test
        $this->assertEquals( 2, $poi['PoiProperty']->count(), 'Should be 2 Property features /cuisine');
        $this->assertEquals( 'cuisine', $poi['PoiProperty'][1]['lookup'], 'Lookup should be cuisine' );
        $this->assertEquals( 'Middle Eastern', $poi['PoiProperty'][1]['value'], 'cuisine Value should be comma seperated' );

        $this->assertEquals( 'features', $poi['PoiProperty'][0]['lookup'], 'Lookup should be feature' );
        $this->assertEquals( 'Good for groups, Vegetarian-friendly, Late-night dining, Private party room(s), Cheap', $poi['PoiProperty'][0]['value'], 'feature Value should be comma seperated' );

        // category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Should be only 1 Category');
        $this->assertEquals( 'Eating & Drinking | New and noteworthy', $poi['VendorPoiCategory'][0]['name'], 'Category name should be Eating & Drinking | New and noteworthy');

        // Check another
        $poi = $pois[6];
        $this->assertEquals( '5806', $poi['vendor_poi_id'], 'Vendor Poi ID missmatch');
        $this->assertEquals( 'Uncle Sammy’s', $poi['poi_name'], 'Poi Name missmatch');
        $this->assertEquals( 'Lunch, dinner - sun-wed 11am-midnight, thu, fri sat 11-2am', $poi['openingtimes'], 'Opening hours missmatch');

        // check Category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count(), 'Default category should be Eating & Drinking');
        $this->assertEquals( 'Eating & Drinking', $poi['VendorPoiCategory'][0]['name'], 'Category name should be Eating & Drinking');

        // Cuisine
        $this->assertEquals( 'cuisine', $poi['PoiProperty'][1]['lookup'], 'Wrong Cuisine lookup!');
        $this->assertEquals( 'Classic American, Delis, Foo', $poi['PoiProperty'][1]['value'], 'Wrong Cuisine value');

    }
}

?>
