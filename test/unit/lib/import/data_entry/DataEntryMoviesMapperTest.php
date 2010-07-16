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

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'sydney',
      'language' => 'en-AU',
      'time_zone' => 'Australia/Sydney',
      'inernational_dial_code' => '+61',
      )
    );
    $vendor->save();

    $importDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR
                  ;
    DataEntryImportManager::setImportDir( $importDir );

    DataEntryImportManager::importMovies( );
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
    $this->assertEquals( 'Культовый в', mb_substr( $movie['review'],0 , 11, 'UTF-8' ) );
    $this->assertEquals( '+04:00', $movie['utf_offset'] );

    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.ru/cinema/event/15032/", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    $this->assertGreaterThan( 0, $movie[ 'MovieGenres' ]->count() );
    $this->assertEquals( "Драма", $movie[ 'MovieGenres' ]['Драма']['genre'] );
    $this->assertEquals( "Кино", $movie[ 'MovieGenres' ]['Кино']['genre'] );

  }
}
