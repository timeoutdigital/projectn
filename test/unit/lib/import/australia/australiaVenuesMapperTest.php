<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

/**
 * Test class for australia venues import
 *
 * @package test
 * @subpackage australia.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 *
 * @version 1.0.1
 */
class australiaVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  private $vendor;
  private $params;

  public function setUp()
  {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('sydney');
        $this->params = array( 'type' => 'poi', 'ftp' => array(
                                                            'classname' => 'FTPClientMock',
                                                            'username' => 'test',
                                                            'password' => 'test',
                                                            'src' => '',
                                                            'dir' => '/',
                                                            'file' => TO_TEST_DATA_PATH . '/sydney/sydney_sample_venues.xml'
                                                            )
            );
        
        // Run Import
        $this->runImport();
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    // load xml for testing
    $xmlFeed = simplexml_load_file( $this->params['ftp']['file'] );
    $this->assertEquals( count( $xmlFeed->venue ),
                         Doctrine::getTable( 'Poi' )->findAll()->count(),
                        'Database should have same number of POIs as feed after import'
                         );

    $poi = Doctrine::getTable( 'Poi' )->findOneById( 1 );

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
    $poi = Doctrine::getTable( 'Poi' )->findOneById( 1 );

    $this->assertEquals( '2010-03-03 12:56:00', $poi[ 'review_date' ] );
  }

  public function testProperties()
  {
    $pois = Doctrine::getTable( 'Poi' )->findAll( );

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
    $pois = Doctrine::getTable( 'Poi' )->findAll( );

    $this->assertEquals( '',                       $pois[0]['price_information'] );
    $this->assertEquals( '$1.00',                   $pois[1]['price_information'] );
    $this->assertEquals( 'between $2.00 and $10.00', $pois[2]['price_information'] );
  }

  public function testHasImages()
  {

    $pois = Doctrine::getTable( 'Poi' )->findAll( );

    $this->assertEquals( 'http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg',
                          $pois[0]['PoiMedia'][0]['url']
                          );
  }

  public function testHasVendorCategories()
  {
    $pois = Doctrine::getTable( 'Poi' )->findAll( );
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
    $importer->addDataMapper( new australiaVenuesMapperMock( $this->vendor, $this->params ) );
    $importer->run();
  }
}


/**
 * Mocking Poi mapper to override _getTheLatestFileName as it require FTP style file listing
 */
class australiaVenuesMapperMock extends australiaVenuesMapper
{
    protected function  _getTheLatestFileName($rawFtpListingOutput, $xmlFileName) {
        return $xmlFileName;
    }
}