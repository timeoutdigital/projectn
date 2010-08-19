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
   * the media added to movie is stored in this array and the largest one will be downloaded in downloadMedia method
   *
   * @var $media
   */
  private $media = array();

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
    $this->downloadMedia();
    $this->removeMultipleImages();

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
   * adds a movie media to the media array and the largest one will be downloaded by downloadMedia method
   *
   * @param string $urlString
   */
  public function addMediaByUrl( $urlString )
  {
    if( empty( $urlString ) )
      return;

    if ( !isset($this[ 'Vendor' ][ 'city' ]) || $this[ 'Vendor' ][ 'city' ] == '' )
    {
        throw new Exception('Failed to add Movie Media due to missing Vendor city, set vendor on the object before calling addMediaUrl()');
    }

    $headers = get_headers( $urlString , 1);
    
    // When Image redirected with 302/301 get_headers will return morethan one header array
    $contentType = ( is_array($headers [ 'Content-Type' ]) ) ? array_pop($headers [ 'Content-Type' ]) : $headers [ 'Content-Type' ];
    $contentLength = ( is_array($headers [ 'Content-Length' ]) ) ? array_pop($headers [ 'Content-Length' ]) : $headers [ 'Content-Length' ];

    // check the header if it's an image
    if( $contentType != 'image/jpeg' )
    {
        return false;
    }

    $this->media[] = array(
        'url'           => $urlString,
        'contentLength' => $contentLength,
        'ident'         => md5( $urlString ),
     );
    return true;
  }


  /**
   * tidy up function for movies with more than one image attached to them
   * read the headers of the images and select the largest one in size
   * remove other images
   *
   */
  private function removeMultipleImages()
  {
     // if there is more than 1 image for this Movie we need to find the largest one and remove the rest
     if( count( $this[ 'MovieMedia' ] ) > 1 )
     {
        $largestImg = $this[ 'MovieMedia' ][ 0 ] ;
        $largestSize = 0;

        foreach ($this[ 'MovieMedia' ] as $movieMedia )
        {
             $headers = get_headers( $movieMedia['url'] , 1);

             if( $headers[ 'Content-Length' ] >  $largestSize)
             {
                $largestSize = $headers[ 'Content-Length' ];
                $largestImg  = $movieMedia;
             }
        }

        $this['MovieMedia'] = new Doctrine_Collection( 'MovieMedia' );

        $this['MovieMedia'] [] = $largestImg;

     }
  }

  /**
   * selects the largest image in media array and downloads the image
   *
   *
   */
  private function downloadMedia()
  {
    // if addMediaByUrl wasn't called, there is no change in media
    if( count( $this->media) == 0 )  return;

    $largestImg = $this->media[ 0 ] ;

    //find the largest image
    foreach ( $this->media as $img )
    {
       if( $img[ 'contentLength' ] > $largestImg[ 'contentLength' ]  )
       {
        $largestImg = $img;
       }
    }

    // check if the largestImg is larger than the one attached already if any
    foreach ($this[ 'MovieMedia' ] as $movieMedia )
    {

        if( $movieMedia['content_length']  > $largestImg[ 'contentLength' ]  )
        {
            //we already have a larger image so ignore this
            return;
        }
    }

    $movieMediaObj = Doctrine::getTable( 'MovieMedia' )->findOneByIdent( $largestImg[ 'ident' ] );

    if ( $movieMediaObj === false )
    {
        $movieMediaObj = new MovieMedia( );
    }
    try
    {
        $movieMediaObj->populateByUrl( $largestImg[ 'ident' ], $largestImg['url'], $this[ 'Vendor' ][ 'city' ] );

        $this[ 'MovieMedia' ] [] =  $movieMediaObj;
    }
    catch ( Exception $e )
    {
        /** @todo : log this error */
    }


  }

}
