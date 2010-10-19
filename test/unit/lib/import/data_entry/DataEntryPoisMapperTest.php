<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of data entry Feed Venues Mapper import.
 *
 * @package test
 *
 * @author emre basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DataEntryPoisMapperTest extends PHPUnit_Framework_TestCase
{

  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    // Load Fixtures to create Vendors
    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'barcelona' );

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;

    $this->object = new DataEntryImportManager( 'barcelona', $importDir);

    $this->object->importPois( );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }


  public function testMapping()
  {
    $pois = Doctrine::getTable('Poi')->findAll();
    $this->assertGreaterThan( 1, $pois->count() );
    $this->assertLessThan( 7, $pois->count() );
    $poi = $pois[0];

    $this->assertEquals( '41.37023910', $poi['latitude'] );
    $this->assertEquals( '2.15708910' , $poi['longitude'] );
    $this->assertNotNull( $poi['longitude'] );
    $this->assertNotNull( $poi['latitude'] );

    $this->assertEquals( 'Museu Arqueologia de Catalunya' , $poi['poi_name'] );


    //address node
    $this->assertEquals( 'Pg. Santa Madrona', $poi['street'] );
    $this->assertEquals( '39-41', $poi['house_no'] );
    $this->assertEquals( 'abcdefg', $poi['zips'] );
    $this->assertEquals( 'Barcelona', $poi['city'] );
    $this->assertEquals( 'Eixample', $poi['district'] );
    $this->assertEquals( 'ESP', $poi['country'] );
    //address end

    //contact node
    $this->assertNotNull( $poi['phone'] );
    $this->assertNotNull( $poi['phone2'] );
    $this->assertNotNull( $poi['fax'] );
    $this->assertNull( $poi['email'] ); // refs #538
    $this->assertEquals( 'http://www.mac.cat', $poi['url'] );
    //contact node end

    //content node
    $this->assertEquals( 'A la Ciutat', $poi['VendorPoiCategory'][0]['name'] );
    $this->assertEquals( 51233,  $poi['vendor_poi_id'] );
    $this->assertEquals( '', $poi['review_date'] );
    $this->assertEquals( 'ca', $poi['local_language'] );

    $this->assertEquals( '', $poi['keywords'] );
    $this->assertEquals( 'a very short description', $poi['short_description'] );
    $this->assertEquals( 'foo bar foo bar foo foo bar23333', $poi['description'] );
    $this->assertEquals( 'Metro Espanya (L1-L3-FGC)', $poi['public_transport_links'] );
    $this->assertEquals( 'not free', $poi['price_information'] );
    $this->assertEquals( 'De dt. a ds., de 9.30 a 19 h. Dg. i fest., de 10 a 14.30 h', $poi['openingtimes'] );
    $this->assertEquals( 0, $poi['star_rating'] );
    $this->assertEquals( 0, $poi['rating'] );
    $this->assertEquals( '', $poi['provider'] );
    $this->assertEquals( $this->vendor['id'], $poi['vendor_id'] );

    $this->assertGreaterThan( 0, $poi[ 'PoiProperty' ]->count() );
    $this->assertEquals( 'http://www.timeout.cat/barcelona/ca/s/viu-barcelona', $poi[ 'PoiProperty' ][0] ['value']  );
    $this->assertEquals( 'Timeout_link', $poi[ 'PoiProperty' ][0] ['lookup']  );

    $this->assertGreaterThan( 0, $poi[ 'PoiMedia' ]->count() );
    $this->assertEquals( 'http://projectn.s3.amazonaws.com/sydney/event/media/30fb4afc231e21b0f88b35e410acfd0540f2c02b.jpg', $poi[ 'PoiMedia' ][0] ['url']  );

    $this->assertEquals(0, preg_match("/ESP/", $poi['geocode_look_up']));
    //content node end

  }

  public function testDataEntryUpdate()
  {

    /** the background of the story :
     * the barcelona data entry import have been called in set up and the venue "Museu Arqueologia de Catalunya" inserted into test database
     * the id of that venue in the database is 1 right?
     * cool!  there is an update XML for updating this venue using dataEntry system the file is in updateXMLs/export_20100712/poi/barcelona.xml
     * for the record, this update XMLs are being created by prepareExportXMLsForDataEntryTask
     * please note that the id in this XML is 1, if  use dataEntryEventMapper with the right configuration  ( app_data_entry_onUpdateFindById = true )we can update the record.
     * the only change is the street name in the update XML
     * here we go!!
     **/
     $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR .
                  'updateXMLs' .DIRECTORY_SEPARATOR
                  ;

    //setting to use the vendor_poi_id to actually update the poi NOT insert a new one!
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,true );

    $this->object = new DataEntryImportManager( 'barcelona', $importDir);

    $this->object->importPois( );

    $poi = Doctrine::getTable( 'Poi' )->find( 1 );

    $this->assertEquals( 'Museu Arqueologia de Catalunya' , $poi[ 'poi_name' ] );

    $this->assertEquals( 'updated street' , $poi[ 'street' ] );
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,false );
  }

}
