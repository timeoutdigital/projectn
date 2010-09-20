<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test for UAE Feed Poi Mapper
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
class UAEFeedPoiMapperTest extends PHPUnit_Framework_TestCase
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

        $fileName =  TO_TEST_DATA_PATH . '/uae/dubai_latest_events-venue.xml';
        // xml data fixer
        $xmlDataFixer = new xmlDataFixer( file_get_contents( $fileName ) );
       
        $dataMapper = new UAEFeedPoiMapper( $this->vendor,   $xmlDataFixer->getSimpleXMLUsingXSLT( sfConfig::get('app_import_xslt_uae_poi')) );
        
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
        $this->assertEquals( 3, $pois->count() ); // 1 repeated!

        $poi = $pois[0];
        $this->assertEquals( '1717', $poi['vendor_poi_id']);
        $this->assertEquals( 'Dubai Dolphinarium', $poi['name']);
        $this->assertEquals( 'A week\'s worth of fun for all the family, including ice-skating, lunch at Planet Hollywood and a blast on the dolphins', $poi['description']);
        $this->assertEquals( '+971 4 336 9773', $poi['phone']);
        $this->assertEquals( 'smo@dubaidolphinarium.ae', $poi['email']);
        $this->assertEquals( 'Al Riyadh Street', $poi['street']);
        $this->assertEquals( 'Oud Metha', $poi['district']);
        $this->assertEquals( null, $poi['openingtimes']); // 2nd venie with same ID hours == null
        $this->assertEquals( null, $poi['price_information']);
        $this->assertEquals( '55.326775', $poi['longitude']);
        $this->assertEquals( '25.234642', $poi['latitude']);

        // property
        $this->assertEquals( 1, $poi['PoiProperty']->count());
        $this->assertEquals( 'http://www.timeoutdubai.com/aroundtown/details/1717-dubai-dolphinarium', $poi['PoiProperty'][0]['value']);
        $this->assertEquals( 'Around Town', $poi['VendorPoiCategory'][0]['name']);

        $poi = $pois[2];
        $this->assertEquals( '955', $poi['vendor_poi_id']);
        $this->assertEquals( 'JamBase', $poi['name']);
        $this->assertEquals( 'JamBase switched music for comedy with the Laughter Factory', $poi['description']);
        $this->assertEquals( '+971 4 366 6914', $poi['phone']);
        $this->assertEquals( 'mjrestaurants@jumeirah.com', $poi['email']);
        $this->assertEquals( 'Al Sufouh Road', $poi['street']);
        $this->assertEquals( 'Jumeirah', $poi['district']);
        $this->assertEquals( 'Show starts 9pm', $poi['openingtimes']); // 2nd venie with same ID hours == null
        $this->assertEquals( null, $poi['price_information']);
        $this->assertEquals( '55.184669', $poi['longitude']);
        $this->assertEquals( '25.135183', $poi['latitude']);

        // property
        $this->assertEquals( 1, $poi['PoiProperty']->count());
        $this->assertEquals( 'http://www.timeoutdubai.com/bars/details/955-jambase', $poi['PoiProperty'][0]['value']);
        $this->assertEquals( 'Bar', $poi['VendorPoiCategory'][0]['name']);
    }
}