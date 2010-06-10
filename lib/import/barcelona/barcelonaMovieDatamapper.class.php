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
class barcelonaMoviesMapper extends barcelonaBaseDataMapper
{

  public function mapMovies()
  {
    foreach( $this->xml->movie as $movieElement )
    {
      try{

          $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], (string) $movieElement['id'] );
          if( $movie === false )
            $movie = new Movie();

          // Column Mapping
          $movie['vendor_movie_id']   = (string) $movieElement['id'];
          $movie['name']              = (string) $movieElement->name;
          $movie['plot']              = $this->fixHtmlEntities( (string) $movieElement->plot );
          //tag line
          $movie['review']            = $this->fixHtmlEntities( (string) $movieElement->review );
          $movie['url']               = (string) $movieElement->url;
          //director
          //writer
          $movie['cast']              = (string) $movieElement->other->cast;
          //age_rating
          //release_date
          //duration
          $movie['country']           = (string) $movieElement->other->country;
          $movie['language']          = (string) $movieElement->other->language;
          //aspect_ratio
          //sound_mix
          //company
          //rating
          $movie[ 'utc_offset' ]      = $this->vendor->getUtcOffset();
          //imdb_id
          $movie['Vendor']            = $this->vendor;

          //genres
          foreach ( $movieElement->genres as $gen )
          {
              $movie->addGenre( (string) $gen->genre );
          }

          // Timeout Link
          if( (string) $movie->timeout_url != "" )
              $movie->setTimeoutLinkProperty( trim( (string) $movie->timeout_url ) );

          //Critics Choice
          $movie->setCriticsChoiceProperty( ( $movie->critics_choice == 'y' ) ? true : false );

          // add original_title as properyt
          if( (string) $movie->original_title != "" )
              $movie->addProperty( 'original_title', trim( (string) $movie->original_title ) );

          //medias

          $this->notifyImporter( $movie );
      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception, $event );
      }
    }
  }

}
