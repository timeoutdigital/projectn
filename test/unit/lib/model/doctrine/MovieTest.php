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

   /**
   * test if setting the name of a Poi ensures HTML entities are decoded
   */
  public function testFixHtmlEntities()
  {
      $movie = ProjectN_Test_Unit_Factory::get( 'Movie' );
      $movie['Vendor'] = ProjectN_Test_Unit_Factory::get( 'Vendor', array( "city" => "Lisbon" ) );
      $movie['name'] = "Movie &quot;name&quot; is";

      // Add HTML Entities to all poi fields of type 'string'
      foreach( Doctrine::getTable( "Movie" )->getColumns() as $column_name => $column_info )
        if( $column_info['type'] == 'string' )
            if( is_string( @$movie[ $column_name ] ) )
                $movie[ $column_name ] .= "&sect;";

      $movie->save();

      $this->assertTrue( preg_match( '/&quot;/', $movie['name'] ) == 0, 'POI name cannot contain HTML entities' );

      // Check HTML Entities for all poi fields of type 'string'
      foreach( Doctrine::getTable( "Movie" )->getColumns() as $column_name => $column_info )
        if( $column_info['type'] == 'string' )
            if( is_string( @$movie[ $column_name ] ) )
                $this->assertTrue( preg_match( '/&sect;/', $movie[ $column_name ] ) == 0, 'Failed to convert &sect; to correct symbol' );
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

   public function testAddMediaByUrlandSavePickLargerImage()
   {
    $movie = ProjectN_Test_Unit_Factory::get( 'Movie' );

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $movie[ 'Vendor' ] = $vendor;

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $mediumImageUrl   = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h217/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $movie->addMediaByUrl( $smallImageUrl );
    $movie->addMediaByUrl( $largeImageUrl );
    $movie->addMediaByUrl( $mediumImageUrl );

    $movie->save();

    $savedMovieId = $movie->id;
    $movie->free( true ); unset( $movie );
    $movie = Doctrine::getTable( "Movie" )->findOneById( $savedMovieId );

    // after adding 3 images we expect to have only one image and it should be the large image
    $this->assertEquals( count( $movie[ 'MovieMedia' ]) ,1 , 'there should be only one MovieMedia attached to a Poi after saving' );
    $this->assertEquals( $movie[ 'MovieMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be attached to POI when adding more than one' );

   }

   /**
    * if there is an image attached to Movie and a smaller one is being added, it should keep the larger image
    *
    */
   public function  testAddMediaByUrlandSaveSkipSmallerImage()
   {
    $movie = ProjectN_Test_Unit_Factory::get( 'Movie' );
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $movie[ 'Vendor' ] = $vendor;

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $movie->addMediaByUrl( $largeImageUrl );
    $movie->save();

    $savedMovieId = $movie->id;
    $movie->free( true ); unset( $movie );
    $movie = Doctrine::getTable( "Movie" )->findOneById( $savedMovieId );

    // adding a smaller size imahe
    $movie->addMediaByUrl( $smallImageUrl );
    $movie->save();

    $this->assertEquals( count( $movie[ 'MovieMedia' ]) ,1 , 'there should be only one MovieMedia attached to a Poi after saving' );
    $this->assertEquals( $movie[ 'MovieMedia' ][0][ 'url' ], $largeImageUrl , 'larger image should be kept adding a smaller sized one' );

   }

    /**
    * if there is an image attached to Movie and a larger one is being added, it should remove the existing image with the larger one
    *
    */
   public function  testAddMediaByUrlandSaveRemoveSmallerImageAndSaveLargerOne()
   {
    $movie = ProjectN_Test_Unit_Factory::get( 'Movie' );
    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $movie[ 'Vendor' ] = $vendor;

    $smallImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h117/image.jpg';
    $largeImageUrl    = 'http://www.toimg.net/managed/images/bounded/10138709/w482/h317/image.jpg';

    $movie->addMediaByUrl( $smallImageUrl );
    $movie->save();

    $savedMovieId = $movie->id;
    $movie->free( true ); unset( $movie );
    $movie = Doctrine::getTable( "Movie" )->findOneById( $savedMovieId );

    // adding a smaller size imahe
    $movie->addMediaByUrl( $largeImageUrl );
    $movie->save();

    $this->assertEquals( count( $movie[ 'MovieMedia' ]) ,1 , 'there should be only one MovieMedia attached to a Poi after saving' );
    $this->assertEquals( $movie[ 'MovieMedia' ][0][ 'url' ], $largeImageUrl , 'larger should be saved' );

   }

}
?>
