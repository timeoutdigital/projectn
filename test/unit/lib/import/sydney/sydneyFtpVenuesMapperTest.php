<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for sydney venues import
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class sydneyFtpVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var SimpleXMLElement
   */
  private $feed;

  /**
   * @var Vendor
   */
  private $vendor;

  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->feed   = simplexml_load_file( TO_TEST_DATA_PATH . '/sydney_sample_venues.xml' );
    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor',  array(
                                                     'city'          => 'sydney',
                                                     'language'      => 'en-AU',
                                                     'country_code'  => 'AUS',
                                                     'inernational_dial_code'  => '+61',
                                                     ) );

    $this->runImport();

    $this->poiTable = Doctrine::getTable( 'Poi' );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $this->assertEquals( count( $this->feed->venue ),
                         $this->poiTable->count(),
                        'Database should have same number of POIs as feed after import'
                         );

    $poi = $this->poiTable->findOneById( 1 );

    $this->assertEquals('Art Gallery of NSW',               $poi['name'],          'Check name field.' );
    $this->assertEquals('1',                                $poi['vendor_poi_id'], 'Check vendor poi id field.' );
    $this->assertEquals('-33.8677263',                      $poi['latitude'],      'Check latitude field.' );
    $this->assertEquals('151.2164369',                      $poi['longitude'],     'Check longitude field.' );
    $this->assertEquals('Sydney',                           $poi['city'],          'Check city field.' );
    $this->assertEquals('AUS',                              $poi['country'],       'Check country field.' );
    $this->assertEquals('+61 2 9225 1700',                  $poi['phone'],         'Check phone field.' );
    $this->assertEquals('2000',                             $poi['zips'],          'Check zips field.' );
    $this->assertEquals('http://www.artgallery.nsw.gov.au', $poi['url'],           'Check url field.' );
    $this->assertEquals('Mon & Tue 10am–5pm; Wed 10am–9pm; Thu–Sun 10am–5pm',
                                                            $poi['openingtimes'],  'Check openingtimes field.' );

    $this->assertEquals('Art Gallery of NSW, Art Gallery Road, The Domain, Sydney, 2000, AUS',
                         $poi['geocode_look_up'],
                        'geocode_look_up test'
                         );

    $this->assertEquals('Art Gallery Road, The Domain',
                         $poi['street'],
                        'Check street field.'
                         );
  }

  public function testRatings()
  {
    $pois = Doctrine::getTable('Poi')->findAll();

    $this->assertEquals('5', $pois[0]['star_rating'],   'Check star_rating field.' );
    $this->assertNull(       $pois[1]['star_rating'],   'Check star_rating field.' );
    $this->assertEquals('3', $pois[2]['star_rating'],   'Check star_rating field.' );
  }

  public function testReviewDate()
  {
    $poi = $this->poiTable->findOneById( 1 );

    $this->assertEquals( '2010-03-03 12:56:00', $poi[ 'review_date' ] );
  }

  public function testProperties()
  {
    $pois = $this->poiTable->findAll( );

    $this->assertEquals( 'http://www.timeoutsydney.com.au/searchall/viewvenue.aspx?venueid=1',
                          $pois[0]['TimeoutLinkProperty'],
                         'Check for Timeout_url' );

    $this->assertNull(   $pois[1]['TimeoutLinkProperty'],
                         'Check for absence of Timeout_url' );

    $this->assertNull(        $pois[0]['CriticsChoiceProperty'] , 'Check that Critics_choice flag is not present' );
    $this->assertEquals( 'Y', $pois[1]['RecommendedProperty'] , 'Check Recommended flag is present' );
    $this->assertEquals( 'Y', $pois[2]['CriticsChoiceProperty'] , 'Check Critics_choice flag is present' );
  }

  public function testPriceInfo()
  {
    $pois = $this->poiTable->findAll( );

    $this->assertEquals( '',                       $pois[0]['price_information'] );
    $this->assertEquals( '$1.00',                   $pois[1]['price_information'] );
    $this->assertEquals( 'between 2.00 and 10.00', $pois[2]['price_information'] );
  }

  public function testHasImages()
  {

    $pois = $this->poiTable->findAll( );

    $this->assertEquals( 'http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg',
                          $pois[0]['PoiMedia'][0]['url']
                          );
  }

  public function testHasVendorCategories()
  {
    $pois = $this->poiTable->findAll( );
    $numVendorCategories = Doctrine::getTable( 'VendorPoiCategory' )->count();

    $this->assertEquals( 'Gallery',
                          $pois[0]['VendorPoiCategory'][0]['name']
                          );

    $this->assertEquals( 'Restaurant | Spanish | Tapas',
                          $pois[1]['VendorPoiCategory'][0]['name']
                          );

    $this->assertEquals(  0,
                          $pois[2]['VendorPoiCategory']->count()
                          );
  }

  public function runImport()
  {
    $importer = new Importer();
    $importer->addDataMapper( new sydneyFtpVenuesMapper( $this->vendor, $this->feed ) );
    $importer->run();
  }
}
