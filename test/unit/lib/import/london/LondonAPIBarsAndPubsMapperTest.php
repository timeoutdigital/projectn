<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API Bars And Pubs Mapper.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPIBarsAndPubsMapperTest extends PHPUnit_Framework_TestCase
{

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine_Manager::connection()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
    Doctrine::loadData( 'data/fixtures/fixtures.yml' );
    ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'london', 'language' => 'en-GB' ) );
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
   * test restaurants are mapped to pois
   */
  public function testMapPoi()
  {
    $importer = new Importer();

    $crawler = new MockLondonAPIBarsAndPubsMapperCrawler();

    $mockGeoEncoder = $this->getMock('geoEncode', array( 'setAddress', 'getLongitude', 'getLatitude' ) );
    $mockGeoEncoder->expects( $this->exactly( 1 ) )
               ->method( 'setAddress' )
               ;
    $mockGeoEncoder->expects( $this->exactly( 1 ) )
               ->method( 'getLongitude' )
               ->will( $this->returnValue( -0.0901 ) )
               ;
    $mockGeoEncoder->expects( $this->exactly( 1 ) )
               ->method( 'getLatitude' )
               ->will( $this->returnValue( 51.35736 ) )
               ;

    $mapper = new LondonAPIBarsAndPubsMapper( $crawler, $mockGeoEncoder );

    $importer->addDataMapper( $mapper );

    $logger = $this->getMock( 'doNothingLogger' );
    $logger->expects( $this->exactly( 0 ) )
           ->method( 'addError' );
    $logger->expects( $this->exactly( 11 ) )
           ->method( 'countNewInsert' );

    $importer->addLogger( $logger );
    $importer->run();

    $poiResults = Doctrine::getTable('Poi')->findAll();

    $this->assertEquals( 11, $poiResults->count() );

    $poi = $poiResults[0];

    $this->assertFalse( empty( $poi[ 'vendor_id' ] ),         'vendor_id should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'vendor_poi_id' ] ),     'vendor_poi_id should not be empty: ' . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'street' ] ),            'street should not be empty: '        . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'city' ] ),              'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'country' ] ),           'city should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'longitude' ] ),         'longitude should not be empty: '     . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'latitude' ] ),          'latitude should not be empty: '      . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'poi_name' ] ),          'poi_name should not be empty: '      . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'url' ] ),               'url should not be empty: '           . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'phone' ] ),             'phone should not be empty: '         . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'zips' ] ),              'zips should not be empty: '          . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'openingtimes' ] ),      'openingtimes should not be empty: '  . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'star_rating' ] ),       'star_rating should not be empty: '   . $poi[ 'url' ] );
    $this->assertFalse( empty( $poi[ 'description' ] ),       'description should not be empty: '   . $poi[ 'url' ] );
    $this->assertEquals( $poi[ 'PoiCategory' ][ 0 ][ 'name' ], 'bar-pub', 'category should be "bar-pub": '   . $poi[ 'url' ] );

    //$this->assertGreaterThan( 0, count( $poi['PoiProperty'] ) ); //need fixtures!
  }

  /**
   * @todo test grabs all restaurants if no limit set
   */
  public function testNoLimit()
  {
    $this->markTestIncomplete();
  }
}

class MockLondonAPIBarsAndPubsMapperCrawler extends LondonAPICrawler
{
  public function crawlApi()
  {
    for( $i=1; $i<=11; $i++ )
    {
      $fileContents = file_get_contents( TO_TEST_DATA_PATH . '/LondonAPIBarsAndPubsTest.xml' );
      $fileContents = str_replace( '{id}', $i, $fileContents );
      $fileContents = str_replace( '{name}', "Poi $i", $fileContents );

      if( $i == 11 )
      {
        $fileContents = preg_replace( ':<lat>[-0-9.]*</lat>:', '', $fileContents );
        $fileContents = preg_replace( ':<lng>[-0-9.]*</lng>:', '', $fileContents );
      }

      $xml = simplexml_load_string( $fileContents );
      $this->mapper->doMapping( $xml->response->row );
    }
  }
}
