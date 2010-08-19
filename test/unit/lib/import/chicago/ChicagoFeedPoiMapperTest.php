<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test for Chicago Poi Mapper
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
class ChicagoFeedPoiMapperTest extends PHPUnit_Framework_TestCase
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

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'chicago' );

    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    /**
    * This test should test the Newly developped Data Mapper using New XML Sample data
    */
    public function testMapPoi()
    {

        // Load XML and Data Mapper
        $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/chicago/chicago_new_event_poi.short.xml' );
        $dataMapper = new ChicagoFeedPoiMapper( $this->vendor, $xml );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();

        $pois = Doctrine::getTable( 'Poi' )->findAll();

        $this->assertEquals(40, $pois->count(), 'Should be 40 Pois as XML has 40 Address'); // Should account for CLOSED tag

        // asert FIRST
        $poi = $pois[0];

        // Assert Details
        $this->assertEquals( '107276', $poi['vendor_poi_id'], 'vendor Poi ID Should be 107276');
        $this->assertEquals( 'Chicago Downstand Farmstand', $poi['poi_name'], 'Poi name is Chicago Downstand Farmstand');
        $this->assertEquals( '66 E Randolph St', $poi['street'], 'Street is 66 E Randolph St');
        $this->assertEquals( 'http://explorechicago.org', $poi['url'], 'Website address should be http://explorechicago.org');
        $this->assertEquals( '+1 312 742 8419', $poi['phone'], 'Telephone should be formated as +01 312 742 8419');
        $this->assertEquals( 'El: Red to Lake; Orange, Pink, Green, Brown, Purple (rush hrs) to Randolph. Bus: 3, 4, 6, 10, 14, 26, 143, 144, 145, 146, 147, 151, 157. Metra: Elec to Millennium Station', $poi['public_transport_links'], 'Public transport links provided as Approach text_type');

        // assert Number 9
        $poi = $pois[8];
        $this->assertEquals( '104979', $poi['vendor_poi_id'], 'vendor Poi ID Should be 104979');
        $this->assertEquals( 'The Spa at the Carlton Club', $poi['poi_name'], 'Poi name is The Spa at the Carlton Club');
        $this->assertEquals( '160 E Pearson St', $poi['street'], 'Street is 160 E Pearson St');
        $this->assertEquals( 'http://www.fourseasons.com/chicagorc/spa.html', $poi['url'], 'Website address should be http://www.fourseasons.com/chicagorc/spa.html');
        $this->assertEquals( '+1 312 573 4195', $poi['phone'], 'Telephone should be formated as +1 312 573 4195');
        $this->assertEquals( 'El: Red to Chicago. Bus: 145, 147, 66', $poi['public_transport_links'], 'Public transport links provided as Approach text_type');
        $this->assertEquals( 'Elegance and sophistication abound at this spa, which is housed inside the Ritz-Carlton. It (annoyingly) decided that you', mb_substr( $poi['description'], 0, 121 ), 'Description is provided as content');
        
        // assert 11
        $poi = $pois[10];
        $this->assertEquals( '104957', $poi['vendor_poi_id'], 'vendor Poi ID Should be 104957');

        $this->assertEquals( 1, $poi['PoiProperty']->count(), 'This poi has an Attribute that should be added to property');
        $this->assertEquals( 'Neighborhoods', $poi['PoiProperty'][0]['lookup'], 'Poi property Lookup should be Neighborhoods' );
        $this->assertEquals( 'West Side', $poi['PoiProperty'][0]['value'], 'Poi property value should be West Side' );

        // Opening hours test
        $this->assertEquals( 'Mon 16:00 - 02:00, Tue 16:00 - 02:00, Wed 16:00 - 02:00, Thu 16:00 - 02:00, Fri 16:00 - 02:00, Sat 16:00 - 03:00, Sun 16:00 - 02:00, Test hours', $poi['openingtimes']);
        
    }
  
}

?>
