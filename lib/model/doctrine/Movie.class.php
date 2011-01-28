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
  public function applyFixes()
  {
    $this->cleanStringFields();
    $this->fixUrl();
    $this->reformatTitle();
    $this->requestImdbId();
    $this->applyOverrides();

  }

  /**
   * Return an Array of column names for which the column type is 'string'
   */
  protected function getStringColumns()
  {
    $column = array();
    foreach( Doctrine::getTable( get_class( $this ) )->getColumns() as $column_name => $column_info )
      if( $column_info['type'] == 'string' )
          $column[$column_name] = $column_info;
    return $column;
  }

  /**
   * Clean all fields of type 'string', Removes HTML and Trim
   */
  protected function cleanStringFields()
  {
    foreach ( $this->getStringColumns() as $field => $field_info )
        if( is_string( @$this[ $field ] ) )
        {
            // fixHTMLEntities
            $this[ $field ] = html_entity_decode( $this[ $field ], ENT_QUOTES, 'UTF-8' );

            // Refs #525 - Trim All Text fields on PreSave
            if($this[ $field ] !== null) $this[ $field ] = stringTransform::mb_trim( $this[ $field ], ',' );

            // Refs #538 - Nullify all Empty string that can be Null in database Schema
            if( $field_info['notnull'] === false && stringTransform::mb_trim( $this[ $field ] ) == '' ) $this[ $field ] = null;
        }

    // Null release_date when empty string found
    $this['release_date'] = ( trim( $this['release_date'] ) == '' ) ? null : $this['release_date'];
  }

  /**
   * Set Timeout Link
   */
  public function setTimeoutLinkProperty( $url )
  {
    if( empty( $url ) )
      return; //@todo consider logging

    $this->addProperty( 'Timeout_link', $url );
  }

  /**
   * Set Critics Choice Link
   */
  public function setCriticsChoiceProperty( $isCriticsChoice )
  {
    if( !is_bool($isCriticsChoice))
      throw new Exception( 'Parameter must be a boolean value.' );

    if( $isCriticsChoice )
      $this->addProperty( 'Critics_choice', 'Y' );
    //@todo else removeProperty
  }

  public function getCriticsChoiceProperty()
  {
    foreach ( $this['MovieProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Critics_choice' )
      {
        return $property[ 'value' ];
      }
    }
  }

  /**
  * PreSave Method
  */
  public function preSave( $event )
  {
    $this->applyFixes();
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
   * selects the largest image in media array and downloads the image
   *
   * @param string $url
   *
   * This function is deprecated in favour of Media::addMedia( $model, $url ).
   * refs #626 -pj 31-Aug-10
   */
  public function addMediaByUrl( $url = "" )
  {
    Media::addMedia( $this, $url );
  }

  public function addMeta( $lookup, $value, $comment = null )
  {
    $metaObj = new MovieMeta();
    $metaObj[ 'lookup' ] = (string) $lookup;
    $metaObj[ 'value' ] = (string) $value;
    if(!is_null($comment) && !is_object($comment))
        $metaObj[ 'comment' ] = (string) $comment;

    $this[ 'MovieMeta' ][] = $metaObj;
  }

}
