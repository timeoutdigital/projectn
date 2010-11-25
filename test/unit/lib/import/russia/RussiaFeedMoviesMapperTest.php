<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test of Russia Feed Movies Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class RussiaFeedMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var RussiaFeedMoviesMapper
   */
  protected $dataMapper;
  protected $vendor;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures'); // Add Initial data
    //$this->addRussianVendors();
    $this->moviesXml = simplexml_load_file( TO_TEST_DATA_PATH . '/russia/russia_movies.short.xml' );
    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'unknown' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

    private function _getParams( $filename )
    {
        return array(
            'type' => 'movie',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/russia/' . $filename
             ),
        );
    }

  public function testMapMovies()
  {
    $this->createVenuesFromVenueIds( $this->getVenueIdsFromXml() );

    $importer = new Importer();
    $importer->addDataMapper( new RussiaFeedMoviesMapper( $this->vendor, $this->_getParams( 'russia_movies.short.xml' ) ) );
    $importer->run();

    $movies = Doctrine::getTable('Movie')->findAll();

    $this->assertEquals( 14, $movies->count() ); // 1 Movie have 3 Venues (14 Venues in 9 Movies = 14 movies should be added)

    $movie = $movies->getFirst();
    $this->assertEquals( 15032,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'Скины', $movie['name'] );
    $this->assertEquals( 'Культовый в', mb_substr( $movie['review'],0 , 11, 'UTF-8' ) );
    $this->assertEquals( $movie['Vendor']->getUtcOffset() , $movie['utf_offset'] ); // UTC Offset changes with daylight saving, it's a good idea to check with current UTC

    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/cinema/event/15032/", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    $this->assertGreaterThan( 0, $movie[ 'MovieGenres' ]->count() );
    $this->assertEquals( "Драма", $movie[ 'MovieGenres' ]['Драма']['genre'] );
    $this->assertEquals( "Кино", $movie[ 'MovieGenres' ]['Кино']['genre'] );

    $movie = $movies[1];

    $this->assertEquals( 41418,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'Небесный замок Лапута', $movie['name'] );
    $this->assertEquals( 'Миядзаки среднего периода', mb_substr( $movie['review'],0 , 25, 'UTF-8' ) );
    $this->assertEquals( $movie['Vendor']->getUtcOffset(), $movie['utf_offset'] ); // UTC Offset changes with daylight saving, it's a good idea to check with current UTC

    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/cinema/event/41418/", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    $this->assertGreaterThan( 0, $movie[ 'MovieGenres' ]->count() );
    $this->assertEquals( "Мультфильм", $movie[ 'MovieGenres' ]['Мультфильм']['genre'] );
    $this->assertEquals( "Кино", $movie[ 'MovieGenres' ]['Кино']['genre'] );

    $this->assertEquals( 2, $movie['MovieProperty']->count(), 'Movie Property should have Timeout_link and English_title' );
    $this->assertEquals( 'Castle in the Sky', $movie['MovieProperty'][1]['value'], 'English name should be added to Movie Property' );

  }

  private function getVenueIdsFromXml()
  {
    $venues = $this->moviesXml->xpath('//venue');
    $venueIds = array();

    foreach( $venues as $venue )
      $venueIds[] = (string) $venue['id'];

    $venueIds = array_unique( $venueIds );
    return $venueIds;
  }

  private function createVenuesFromVenueIds( $venueIds )
  {
    $russianVendors = Doctrine::getTable( 'Vendor' )->findByLanguage( 'ru' );
    $this->assertEquals( 8, $russianVendors->count(), 'Should have 8 Russian Vendors' );

    $vendorNumber = 0;

    foreach( $venueIds as $venueId )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'vendor_poi_id' ] = (int) $venueId;

      if( $vendorNumber >= $russianVendors->count() ) $vendorNumber = 0;
      else $vendorNumber++;

      $poi[ 'Vendor' ] = $russianVendors[ $vendorNumber ];
      $poi->save();
    }

    $this->assertEquals( count( $venueIds ), Doctrine::getTable( 'Poi' )->count() );
  }
}
?>
