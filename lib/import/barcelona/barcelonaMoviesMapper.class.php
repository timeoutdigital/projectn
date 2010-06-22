<?php
/**
 * Barcelona movie mapper
 *
 * @package projectn
 * @subpackage barcelona.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class barcelonaMoviesMapper extends barcelonaBaseMapper
{

  public function mapMovies()
  {
    for( $i=0, $movieElement = $this->xml->movie[ 0 ]; $i<$this->xml->movie->count(); $i++, $movieElement = $this->xml->movie[ $i ] )
    {
      try{

          $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $this->clean( (string) $movieElement['id'] ) );
          if( $movie === false )
            $movie = new Movie();

          // Column Mapping
          $movie['vendor_movie_id']   = $this->clean( (string) $movieElement['id'] );
          $movie['name']              = $this->clean( (string) $movieElement->name );
          $movie['plot']              = $this->fixHtmlEntities( $this->clean( (string) $movieElement->plot ) );
          //tag line
          $movie['review']            = $this->fixHtmlEntities( $this->clean( (string) $movieElement->review ) );
          $movie['url']               = $this->clean( (string) $movieElement->url );
          //director
          //writer
          $movie['cast']              = $this->clean( (string) $movieElement->other->cast );
          //age_rating
          //release_date
          //duration
          $movie['country']           = $this->clean( (string) $movieElement->other->country );
          $movie['language']          = $this->clean( (string) $movieElement->other->language );
          //aspect_ratio
          //sound_mix
          //company
          //rating
          $movie[ 'utf_offset' ]      = $this->vendor->getUtcOffset();
          //imdb_id
          $movie['Vendor']            = clone $this->vendor;

          // Add Genres
          foreach ( $movieElement->genres as $gen )
              $movie->addGenre( $this->clean( (string) $gen->genre ) );

          // Timeout Link
          if( (string) $movieElement->timeout_url != "" )
              $movie->setTimeoutLinkProperty( $this->clean( (string) $movieElement->timeout_url ) );

          //Critics Choice
          $movie->setCriticsChoiceProperty( strtolower( $this->clean( $movieElement->critics_choice ) ) == 'y' );

          // Add 'original_title' as a property
          if( $this->clean( (string) $movieElement->other->original_title ) != "" )
              $movie->addProperty( 'Original_title', $this->clean( (string) $movieElement->other->original_title ) );

          // Add 'year' as a property
          if( $this->clean( (string) $movieElement->other->year ) != "" )
              $movie->addProperty( 'Year', $this->clean( (string) $movieElement->other->year ) );

          $this->notifyImporter( $movie );
      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception, $movie );
      }
    }
  }

}
