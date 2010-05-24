<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class RussiaFeedMoviesMapper extends RussiaFeedBaseMapper
{
  public function mapMovies()
  {
    foreach( $this->xml->movie as $movieElement )
    {
        try {
            // Get Movie Id
            $vendor_movie_id = (int) $movieElement['id'];

            $movie = Doctrine::getTable("Movie")->findByVendorMovieIdAndVendorLanguage( $vendor_movie_id, 'ru' );
            if( !$movie ) $movie = new Movie();

            // Column Mapping
            $movie['vendor_movie_id']   = $vendor_movie_id;
            $movie['name']              = (string) $movieElement->name;
            $movie['plot']              = $this->fixHtmlEntities( (string) $movieElement->plot );
            $movie['review']            = $this->fixHtmlEntities( (string) $movieElement->review );
            $movie['url']               = (string) $movieElement->url;
            $movie['rating']            = (string) $movieElement->rating;

            // Booking Url
            if( (string) $movieElement->booking_url != "" )
                $movie->addProperty( "Booking_url" , (string) $movieElement->booking_url );

            // Timeout Link
            if( (string) $movieElement->timeout_url != "" )
                $movie->addProperty( "Timeout_link", (string) (string) $movieElement->timeout_url );

            // Add First Image Only
            $medias = array();
            foreach( $movieElement->medias->media as $media ) $medias[] = (string) $media;
            if( !empty( $medias ) ) $this->addImageHelper( $movie, $medias[0] );

            // Attach Venues
            foreach( $movieElement->venues->venue as $xmlVenue )
            {
                // Get Occurrence Id
                $vendor_venue_id = (int) $xmlVenue['id'];
                if( !isset( $vendor_venue_id ) || !is_numeric( $vendor_venue_id ) ) break;

                $poi = Doctrine::getTable("Poi")->findByVendorPoiIdAndVendorLanguage( $vendor_venue_id, 'ru' );
                if( !$poi )
                {
                    $this->notifyImporterOfFailure( new Exception( "Could not find POI for Movie with Id: " . $vendor_movie_id ) );
                    break;
                }

                $movie['Vendor'] = $poi['Vendor'];

                // Genres (Requires Vendor)
                foreach( $movieElement->categories->category as $category )
                    $movie->addGenre( (string) $category, $movie['Vendor']['id'] );

                // UTC Offset (Requires Vendor)
                $movie['utf_offset']        = (string) $movie['Vendor']->getUtcOffset();
                
                $this->notifyImporter( $movie );

                $movie = $movie->copy();
            }
            unset( $movie );
        }
        catch( Exception $exception )
        {
            echo $exception->getMessage() . PHP_EOL;
            //$this->notifyImporterOfFailure( $exception );
        }
    }
  }
}
?>
