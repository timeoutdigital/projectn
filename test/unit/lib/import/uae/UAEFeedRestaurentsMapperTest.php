<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test for UAE Feed Restaurents Mapper
 *
 * @package test
 * @subpackage uae.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class UAEFeedRestaurantsMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );

        $fileName =  TO_TEST_DATA_PATH . '/uae/dubai_restaurents.xml';

        // xml data fixer
        $xmlDataFixer = new xmlDataFixer( file_get_contents( $fileName ) );
        $dataMapper = new UAEFeedRestaurantsMapper( $this->vendor, $xmlDataFixer->getSimpleXML() );
        
        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
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

        $this->assertEquals( null, $poi['latitude']);
        $this->assertEquals( null, $poi['longitude']);

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