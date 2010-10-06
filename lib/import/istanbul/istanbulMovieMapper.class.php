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
                $movie->addGenre( $genreName );
            }
          }

          // Timeout Link
          if( (string) $movieElement->timeout_url != "" )
          {
            $movie->setTimeoutLinkProperty( $this->clean( (string) $movieElement->timeout_url ) );
          }

          //medias
          foreach( $movieElement->medias->media as $media )
          {
             $movie->addMediaByUrl( (string) $media );
          }

          $this->notifyImporter( $movie );

      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception, $movie );
      }
    }
  }
}

