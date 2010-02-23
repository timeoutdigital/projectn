<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for Lisbon Feed Movies Mapper.
 *
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LisbonFeedMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedMoviesMapper
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'Lisbon',
      'language' => 'pt',
      'time_zone' => 'Europe/Lisbon',
      )
    );
    $vendor->save();
    $this->vendor = $vendor;

    ProjectN_Test_Unit_Factory::add( 'poi', array( 'vendor_poi_id' => '1153' ) );
    ProjectN_Test_Unit_Factory::add( 'poi', array( 'vendor_poi_id' => '1170' ) );
    ProjectN_Test_Unit_Factory::add( 'poi', array( 'vendor_poi_id' => '1140' ) );
    ProjectN_Test_Unit_Factory::add( 'poi', array( 'vendor_poi_id' => '1175' ) );

    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_films.short.xml' );
    $this->object = new LisbonFeedMoviesMapper( $xml );
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
   * @todo fix weird loop bug, the commented out count below should
   * work but some items appear to be looped over twice
   *
   * test mapMovies has required fields and properties
   */
  public function testMapMovies()
  {
    /*$importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $movies = Doctrine::getTable( 'Movie' )->findAll();
    //$this->assertEquals( 2, $movies->count() );

    $movie = $movies[0];

    $this->assertEquals( '11316', $movie['vendor_movie_id'] );
    $this->assertEquals( 'O Exército do Crime', $movie['name'] );
    $this->assertEquals( '1', $movie['poi_id'] );
    //$this->assertEquals( '2.2', $movie['rating'] );
    $this->assertEquals( '', $movie['age_rating'] );

    $this->assertGreaterThan( 0, $movie['MovieProperty']->count() );

    $properties = $movie[ 'MovieProperty' ];
    $this->assertGreaterThan( 0, $properties->count() );*/
  }
}
?>
