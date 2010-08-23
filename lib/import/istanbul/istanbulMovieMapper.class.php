<?php
/**
 * Istanbul DataMapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */

class istanbulMovieMapper extends istanbulBaseMapper
{
  public function mapMovies()
  {
    for( $i=0, $movieElement = $this->xml->movie[ 0 ]; $i<$this->xml->movie->count(); $i++, $movieElement = $this->xml->movie[ $i ] )
    {
      $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $this->clean( (string) $movieElement['id'] ) );
      if( $movie === false )
      {
        $movie = new Movie();
      }

      try{

          // Column Mapping
          $movie['vendor_movie_id']   = $this->clean( (string) $movieElement['id'] );
          $movie['name']              = $this->clean( (string) $movieElement->name );
          $movie['review']            = $this->fixHtmlEntities( $this->clean( (string) $movieElement->review ) );
          $movie['url']               = $this->clean( (string) $movieElement->url );
          //$movie['cast']              = $this->clean( (string) $movieElement->other->cast );
          //$movie['country']           = $this->clean( (string) $movieElement->other->country );
          //$movie['language']          = $this->clean( (string) $movieElement->other->language );
          $movie[ 'utf_offset' ]      = $this->vendor->getUtcOffset();
          $movie['Vendor']            = clone $this->vendor;

          // Add Genres
          foreach ( $movieElement->genres->genre as $gen )
          {
            $genreName = $this->clean( (string) $gen->name );
            if( !empty( $genreName ) )
            {
                var_dump( $genreName );
                $movie->addGenre( $genreName );
            }
          }

          // Timeout Link
          if( (string) $movieElement->timeout_url != "" )
          {
            $movie->setTimeoutLinkProperty( $this->clean( (string) $movieElement->timeout_url ) );
          }

          foreach( $movieElement->medias->media as $media )
          {
             $movie->addMediaByUrl( (string) $media );
          }

          //Critics Choice
          //$movie->setCriticsChoiceProperty( strtolower( $this->clean( $movieElement->critics_choice ) ) == 'y' );

          // Add 'original_title' as a property
          //if( $this->clean( (string) $movieElement->other->original_title ) != "" )
          //    $movie->addProperty( 'Original_title', $this->clean( (string) $movieElement->other->original_title ) );

          // Add 'year' as a property
          //if( $this->clean( (string) $movieElement->other->year ) != "" )
          //    $movie->addProperty( 'Year', $this->clean( (string) $movieElement->other->year ) );

          //$this->notifyImporter( $movie );
          $movie->save();
      }
      catch( Exception $exception )
      {
          var_dump($exception->getMessage());
          $this->notifyImporterOfFailure( $exception, $movie );
      }
    }
  }
}

