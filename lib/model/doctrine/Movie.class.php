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
    $this->fixUrl();
    $this->reformatTitle();
    $this->requestImdbId();
    $this->applyOverrides();
    $this->downloadMedia();
    $this->removeMultipleImages();

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

    // check the header if it's an image
    if( $headers [ 'Content-Type' ] != 'image/jpeg' )
    {
        return ;
    }

    $this->media[] = array(
        'url'           => $urlString,
        'contentLength' => $headers[ 'Content-Length' ],
        'ident'         => md5( $urlString ),
     );
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

    $movieMediaObj->populateByUrl( $largestImg[ 'ident' ], $largestImg['url'], $this[ 'Vendor' ][ 'city' ] );

    $this[ 'MovieMedia' ] [] =  $movieMediaObj;

  }

}
