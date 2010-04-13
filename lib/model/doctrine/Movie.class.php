<?php

/**
 * Custom Movie Model
 * 
 * @package    projectn
 * @subpackage model.doctrine.lib
 * @author     Tim Bowler <timbowler@timeout.com>
 *
 * @version    1.0.1
 *
 * @todo Add tests for addProperty();
 */
class Movie extends BaseMovie
{

  private $externalSearchClass = 'IMDB';

  /**
  * Attempts to fix and / or format fields, e.g. url
  */
  public function preSave( $event )
  {
    $this->fixUrl();
    $this->reformatTitle();
    $this->requestImdbId();
    $this->applyOverrides();
  }

  private function applyOverrides()
  {
    $override = new recordFieldOverrideManager( $this );
    $override->applyOverridesToRecord();
  }

  public function setExternalSearchClass( $externalSearchClass )
  {
    $this->externalSearchClass = $externalSearchClass;
  }

  private function requestImdbId()
  {
    if( $this[ 'imdb_id' ] )
    {
      return;
    }

    $movie = IMDB::findMovieByTitle( $this[ 'name' ] );

    if( !is_null( $movie ) )
    {
      $this[ 'imdb_id' ] = $movie->getId();
    }
    else
    {
      $this[ 'imdb_id' ] = null;
    }
  }

  private function reformatTitle()
  {
     $this['name'] = stringTransform::move_CommaThe_FromEndToBeginning( $this['name'] );
  }

  private function fixUrl()
  {
     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
  }

   /* Add a property to a movie
   *
   * @param string $lookup
   * @param string $value
   */
  public function addProperty( $lookup, $value)
  {
    if( $this->exists() )
    {
      foreach( $this['MovieProperty'] as $property )
      {
        $lookupIsSame = ( $lookup == $property[ 'lookup' ] );
        $valueIsSame  = ( $value  == $property[ 'value' ]  );

        if( $lookupIsSame && $valueIsSame )
        {
          return;
        }
      }
    }
    $moviePropertyObj = new MovieProperty();
    $moviePropertyObj[ 'lookup' ] = (string) $lookup;
    $moviePropertyObj[ 'value' ] = (string) $value;

    $this[ 'MovieProperty' ][] = $moviePropertyObj;
  }

  /**
   * add genre reference to movie and add the actual genre itself too, if
   * none existent
   *
   * @param string $genre
   */
  public function addGenre( $genre )
  {
    $movieGenreObj = Doctrine::getTable( 'MovieGenre' )->findOneByGenre( $genre );

    if ( $movieGenreObj === false )
    {
      $movieGenreObj = new MovieGenre();
      $movieGenreObj[ 'genre' ] = $genre;
      
      // set key column to value to avoid duplicat adds
      // this line makes it unique alredy, no need for manual check with contains()
      $this[ 'MovieGenres' ]->setKeyColumn( 'genre' );
    }

    $this[ 'MovieGenres' ][] = $movieGenreObj;            
  }

  /**
   * adds a movie media and invokes the download for it
   *
   * @param string $urlString
   */
  public function addMediaByUrl( $urlString )
  {
    if ( !isset($this[ 'Vendor' ][ 'city' ]) || $this[ 'Vendor' ][ 'city' ] == '' )
    {
        throw new Exception('Failed to add Movie Media due to missing Vendor city, set vendor on the object before calling addMediaUrl()');
    }

    $identString = md5( $urlString );
    $movieMediaObj = Doctrine::getTable( 'MovieMedia' )->findOneByIdent( $identString );

    if ( $movieMediaObj === false )
    {
      foreach( $this['MovieMedia'] as $movieMedia )
      {
        if( $identString == $movieMedia[ 'ident' ] )
        {
          return;
        }
      }
      $movieMediaObj = new MovieMedia(); 
    }

    $movieMediaObj->populateByUrl( $identString, $urlString, $this[ 'Vendor' ][ 'city' ] );
    $this[ 'MovieMedia' ][] = $movieMediaObj;
  }

}
