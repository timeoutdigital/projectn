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
        $this->assertEquals(4, Doctrine::getTable( 'Poi' )->count() );
        
        $pois = Doctrine::getTable( 'Poi' )->findAll();
        
        $poi = $pois[0];
        $this->assertEquals( 'Old Sugar Refinery', $poi['poi_name']);
        $this->assertEquals( null, $poi['house_no']);
        $this->assertEquals( 'Zalka sea side roa', $poi['street']);
        $this->assertEquals( 'beirut', $poi['city']);
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

        $this->assertEquals( 1, $poi['Poiproperty']->count() , 'Should have 1 property. 1: Town '); // No timeout link for this poi
        $this->assertEquals( 'Town', $poi['PoiProperty'][0]['lookup']);
        $this->assertEquals( 'Zalka', $poi['PoiProperty'][0]['value']);
    }
}