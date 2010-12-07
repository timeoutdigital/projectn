<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Beirut Poi mapper test
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

class BeirutFeedVenueMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    private $params;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        // Set up vendor and params
        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'beirut' );
        $this->params = array( 'type' => 'poi', 'curl' => array(
                                                                'classname' => 'CurlMock',
                                                                'src' => TO_TEST_DATA_PATH . '/beirut/venue.xml',
                                                                ) );

        // Import Data
        $importer = new Importer( );
        $importer->addDataMapper( new BeirutFeedVenueMapper( $this->vendor, $this->params ) );
        $importer->run();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapVenue()
    {
        $this->assertEquals(5, Doctrine::getTable( 'Poi' )->count() );
        
        $pois = Doctrine::getTable( 'Poi' )->findAll();
        
        $poi = $pois[0];
        $this->assertEquals( 'Old Sugar Refinery', $poi['poi_name']);
        $this->assertEquals( null, $poi['house_no']);
        $this->assertEquals( 'Zalka sea side road', $poi['street']);
        $this->assertEquals( 'Beirut', $poi['city']);
        $this->assertEquals( 'Maten', $poi['district']);
        $this->assertEquals( 'before Mazda showroom', $poi['additional_address_details']);
        $this->assertEquals( 'LBN', $poi['country']);
        $this->assertEquals( null, $poi['zips']);
        $this->assertEquals( 'Zalka', $poi['short_description']);
        $this->assertEquals( null, $poi['description']);
        $this->assertEquals( null, $poi['price_information']);
        $this->assertEquals( null, $poi['openingtimes']);
        $this->assertEquals( null, $poi['rating']);
        $this->assertEquals( null, $poi['email']);
        $this->assertEquals( null, $poi['phone']);
        $this->assertEquals( null, $poi['phone2']);
        $this->assertEquals( null, $poi['fax']);
        $this->assertEquals( null, $poi['url']);
        $this->assertEquals( null, $poi['review_date']);
        $this->assertEquals( null, $poi['public_transport_links']);
        
        $this->assertEquals( 1, $poi['PoiProperty']->count() , 'Should have 1 property. 1: Town '); // No timeout link for this poi
        $this->assertEquals( 'Town', $poi['PoiProperty'][0]['lookup']);
        $this->assertEquals( 'Zalka', $poi['PoiProperty'][0]['value']);

        // test another POI
        $poi = $pois[3];
        $this->assertEquals( '906', $poi['vendor_poi_id']);
        $this->assertEquals( 'Bar Louie', $poi['poi_name']);
        $this->assertEquals( null, $poi['house_no']);
        $this->assertEquals( 'Gouraud Street', $poi['street']);
        $this->assertEquals( 'Beirut', $poi['city']);
        $this->assertEquals( 'Gemmayzeh', $poi['district']);
        $this->assertEquals( 'Gemmayzeh', $poi['additional_address_details']);
        $this->assertEquals( 'LBN', $poi['country']);
        $this->assertEquals( null, $poi['zips']);
        $this->assertEquals( 'One of the first pubs to open in Gemmayzeh, Bar Louie has remained open through thick and thin. The restaurant features a delicious mix of Spanish, French, American and Lebanese food, and at night you can kick back while a live band plays jazz, blues and Latin music. Bar Louie is truly a Gemmayzeh legend.', $poi['short_description']);
        $this->assertEquals( null, $poi['description']);
        $this->assertEquals( null, $poi['price_information']);
        $this->assertEquals( null, $poi['openingtimes']);
        $this->assertEquals( null, $poi['rating']);
        $this->assertEquals( null, $poi['email']);
        $this->assertEquals( '961 157 5877', $poi['phone']);
        $this->assertEquals( '961 379 1998', $poi['phone2']);
        $this->assertEquals( null, $poi['fax']);
        $this->assertEquals( null, $poi['url']);
        $this->assertEquals( null, $poi['review_date']);
        $this->assertEquals( null, $poi['public_transport_links']);
        
        $this->assertEquals( 0, $poi['PoiProperty']->count() , 'When cityname == beirut, we should not add this as Town');
        
        $this->assertEquals( '35.512903', $poi['longitude']);
        $this->assertEquals( '33.894873', $poi['latitude']);

        // test category
        $this->assertEquals( 1, $poi['VendorPoiCategory']->count());
        $this->assertEquals( 'Restaurants', $poi['VendorPoiCategory'][0]['name']);


    }
}