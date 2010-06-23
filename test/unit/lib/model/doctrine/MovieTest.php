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

  public function testAddTimeoutUrl()
  {
    $this->assertEquals( 0, count( $this->object['MovieProperty'] ) );

    $url = "http://www.example.com";
    $this->object->setTimeoutLinkProperty( $url );

    $this->assertEquals( 1, count( $this->object['MovieProperty'] ) );
    $this->assertEquals( 'Timeout_link', $this->object['MovieProperty'][0]['lookup'] );
    $this->assertEquals( $url, $this->object['MovieProperty'][0]['value'] );
  }

  /**
   *
   * test the  getter and setter functions for the Critics_choice flag
   */
  public function testSetterGetterCriticsChoiceFlag()
  {
    $this->object['CriticsChoiceProperty'] = true;
    $this->assertEquals( 'Y', $this->object['CriticsChoiceProperty'] );

    //see todo in subject class
    //$this->object['CriticsChoiceProperty'] = false;
    //$this->assertNull( $this->object['CriticsChoiceProperty'] );

    $this->setExpectedException( 'Exception' );
    $this->object->setCriticsChoiceProperty( 'not a boolean' );
    $this->assertNull( $this->object->getCriticsChoiceProperty() );
  }

  /**
   * 
   */
  public function testShouldNotLookupImdbIfImdbIdExists()
  {
    $manualId = 'tt8098';

    $movie = ProjectN_Test_Unit_Factory::get( 'Movie' );
    $movie[ 'imdb_id' ] = $manualId;
    $movie->save();

    $this->assertEquals( $manualId, $movie[ 'imdb_id' ] );
  }

  public function testMovieFindsImdbTitle()
  {
    $movie = $this->object;

    $movie[ 'name' ] = 'Avatar';
    $movie->save();
    $this->assertEquals( 'tt0499549', $movie[ 'imdb_id' ] );
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
}
?>
