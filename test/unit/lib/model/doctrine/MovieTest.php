<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Movie Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class MovieTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Movie
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->object = ProjectN_Test_Unit_Factory::get( 'Movie' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * test if the add property adds the properties
   */
  public function testAddProperty()
  {
    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('Movie')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test prop lookup', $this->object[ 'MovieProperty' ][ 0 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value', $this->object[ 'MovieProperty' ][ 0 ][ 'value' ] );

    $this->assertEquals( 'test prop lookup 2', $this->object[ 'MovieProperty' ][ 1 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value 2', $this->object[ 'MovieProperty' ][ 1 ][ 'value' ] );
  }

  /**
   * tests addDirectorProperty()
   */
  public function testAddDirectorProperty()
  {
    $director = 'Michael Bay';
    $this->object->addDirectorProperty( $director );
    $this->assertEquals( 1, count( $this->object['MovieProperty'] ) );
    $this->assertEquals( 'Director', $this->object['MovieProperty'][0]['lookup'] );
    $this->assertEquals( $director, $this->object['MovieProperty'][0]['value'] );
  }

  /**
   * tests addRuntimeProperty()
   */
  public function testAddRuntimeProperty()
  {
    $runtime = '0';
    $this->object->addRuntimeProperty( $runtime );
    $this->assertEquals( 0, count( $this->object['MovieProperty'] ), 'Runtime property should not be added if value is 0' );

    $runtime = 'a string';
    $this->object->addRuntimeProperty( $runtime );
    $this->assertEquals( 0, count( $this->object['MovieProperty'] ), 'Runtime property should not be added if value is type string' );

    $runtime = '1';
    $this->object->addRuntimeProperty( $runtime );
    $this->assertEquals( 1, count( $this->object['MovieProperty'] ), 'Runtime property is saved if value is integer' );
    $this->assertEquals( 'Runtime', $this->object['MovieProperty'][0]['lookup'] );
    $this->assertEquals( $runtime, $this->object['MovieProperty'][0]['value'] );
  }

  /**
   * tests addCastProperty()
   */
  public function testAddCastProperty()
  {
    $cast = 'Jennifer Lopez, James Spade, Warren G';
    $this->object->addCastProperty( $cast );
    $this->assertEquals( 1, count( $this->object['MovieProperty'] ), 'Cast property is saved' );
    $this->assertEquals( 'Cast', $this->object['MovieProperty'][0]['lookup'] );
    $this->assertEquals( $cast, $this->object['MovieProperty'][0]['value'] );
  }

  /**
   * tests addYearProperty()
   */
  public function testAddYearProperty()
  {
    $year = 'foo';
    $this->object->addYearProperty( $year );
    $this->assertEquals( 0, count( $this->object['MovieProperty'] ), 'Year property is not saved if is string' );

    $year = 0;
    $this->object->addYearProperty( $year );
    $this->assertEquals( 0, count( $this->object['MovieProperty'] ), 'Year property is not saved if value is 0' );

    $year = 1930;
    $this->object->addYearProperty( $year );
    $this->assertEquals( 1, count( $this->object['MovieProperty'] ), 'Year property is saved if value is four digit integer' );
    $this->assertEquals( 'Year', $this->object['MovieProperty'][0]['lookup'], 'Year property is saved if value is four digit integer' );
    $this->assertEquals( $year,  $this->object['MovieProperty'][0]['value'], 'Year property is saved if value is four digit integer' );
  }

  /**
   * tests addAgeRatingProperty()
   */
  public function testAddAgeRatingProperty()
  {
    $ageRating = 'age rating';
    $this->object->addAgeRatingProperty( $ageRating );
    $this->assertEquals( 1, count( $this->object['MovieProperty'] ), 'Age Rating property is saved' );
    $this->assertEquals( 'Age_rating', $this->object['MovieProperty'][0]['lookup'] );
    $this->assertEquals( $ageRating, $this->object['MovieProperty'][0]['value'] );
  }

  /*
   * test if the genres are added (w/a duplicates)
   */
  public function testAddGenres()
  {
    $this->object->addGenre( 'test' );
    $this->object->addGenre( 'test2' );
    $this->object->addGenre( 'test' );
    $this->object->save();

    $this->assertEquals( 2, Doctrine::getTable('MovieGenre')->findAll()->count() );
  }

  public function testMovieTitleIsReformatted()
  {
    $movie = $this->object;

    $movie[ 'name' ] = 'Wolfman, The';
    $movie->save();

    $this->assertEquals( 'The Wolfman', $movie[ 'name' ] );
  }

  public function testMovieFindsImdbTitle()
  {
    $movie = $this->object;

    $movie[ 'name' ] = 'Avatar';
    $movie->save();
    $this->assertEquals( 'tt0499549', $movie[ 'imdb_id' ] );
  }
}
?>
