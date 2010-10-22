<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of data entry Feed Movies Mapper import.
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
class DataEntryMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedVenuesMapper
   */
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

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'sydney' );

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;

    $this->object = new DataEntryImportManager( 'sydney', $importDir );

    $this->object->importMovies( );
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
    $movies= Doctrine::getTable('Movie')->findAll();
    $this->assertEquals( 2, $movies->count() );
    $movie = $movies[ 0 ];

    $this->assertEquals( 2882,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'Bright Star', $movie['name'] );
    $this->assertEquals( 'Action movie addicts who do evil in this life should be forced to watch Bright Star over and over in the next.',  $movie['review'] );

    $genres = $movie[ 'MovieGenres' ]->toArray();

    $this->assertEquals( 2, count( $genres) );

    $this->assertEquals( 'genre1' , $genres['genre1']['genre'] );
    $this->assertEquals( 'genre2' , $genres['genre2']['genre'] );

    $this->assertEquals( $this->vendor->getUtcOffset(), $movie['utf_offset'] );

    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/cinema/event/15032/", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    $this->assertEquals( 2, count( $movie[ 'MovieGenres' ] ) );
    $this->assertEquals( 'sample tag-line string goes here', $movie[ 'tag_line' ] );
    $this->assertEquals( 'sample plot string goes there', $movie[ 'plot' ] );
    $this->assertEquals( '4', $movie[ 'rating' ] );
    $this->assertNull( $movie[ 'director' ] ); // refs #538
    $this->assertEquals( 'Anne-Marie Duff', $movie[ 'writer' ] );
    $this->assertEquals( 'Abbie Cornish, Ben Whishaw', $movie[ 'cast' ] );

    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( 'http://www.timeout.ru/cinema/event/15032/', $movie[ 'MovieProperty' ][0] ['value']  );
    $this->assertEquals( 'Timeout_link', $movie[ 'MovieProperty' ][0] ['lookup']  );

    $this->assertGreaterThan( 0, count( $movie[ 'MovieMedia' ]->count() ) );
    $this->assertEquals( 'http://projectn.s3.amazonaws.com/sydney/event/media/a0572eede1f29f5886f82655f34e4d9329f787b7.jpg', $movie[ 'MovieMedia' ][0] ['url']  );

    $this->assertEquals( 'http://www.google.com', $movie[ 'url' ] );
    $this->assertEquals( 'PG', $movie[ 'age_rating' ] );
    $this->assertEquals( '119 mins', $movie[ 'duration' ] );
    $this->assertEquals( 'english', $movie[ 'language' ] );
    $this->assertEquals( 'aspect-ratio-string', $movie[ 'aspect_ratio' ] );
    $this->assertEquals( 'sound-mix-string', $movie[ 'sound_mix' ] );
    $this->assertEquals( 'string-for-company-name', $movie[ 'company' ] );

  }

  public function testDataEntryUpdate()
  {

    /** if this test doesn't make any sense please look into the test with the same name in DataEntryPoiMapperTest. there is some explanation there
     * and it's basicly the same thing here
     * in the update XML the name is changed and added a new actor to cast
     **/
     $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR .
                  'updateXMLs' .DIRECTORY_SEPARATOR
                  ;

    //setting to use the vendor_movie_id to actually update the movie NOT insert a new one!
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,true );

    $this->object = new DataEntryImportManager( 'sydney', $importDir);

    $this->object->importMovies( );

    $movie = Doctrine::getTable( 'Movie' )->find( 1 );

    $this->assertEquals( 'Bright Star - very bright' , $movie[ 'name' ] );

    //john Doe is new
    $this->assertEquals( 'Abbie Cornish, Ben Whishaw, john Doe' , $movie[ 'cast' ] );
    sfConfig::set( 'app_data_entry_onUpdateFindById' ,false );
  }
}
