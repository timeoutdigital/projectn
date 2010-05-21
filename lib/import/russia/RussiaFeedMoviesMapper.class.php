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
            foreach( $movieElement->attributes() as $k => $v )
                if( $k == "id" ) $vendor_movie_id = (int) $v;

            if( !isset( $vendor_movie_id ) || !is_numeric( $vendor_movie_id ) ) break;

            $poi = $this->dataMapperHelper->getMovieRecord( $vendor_movie_id );

            // Column Mapping
            $movie['Vendor']            = $this->vendor;
            $movie['vendor_movie_id']   = $vendor_movie_id;
            $movie['name']              = (string) $movieElement->name;
            $movie['plot']              = (string) $movieElement->plot;
            $movie['review']            = (string) $movieElement->review;
            $movie['url']               = (string) $movieElement->url;
            $movie['rating']            = (string) $movieElement->rating;
            $movie['utf_offset']        = (string) $this->vendor->getUtcOffset();

            // Booking Url
            if( (string) $movieElement->booking_url != "" )
                $movie->addProperty( "Booking_url" , (string) $movieElement->booking_url );

            // Timeout Link
            if( (string) $movieElement->timeout_url != "" )
                $movie->addProperty( "Timeout_link", (string) (string) $movieElement->timeout_url );

            // Categories
            $categories = array();
            foreach( $movieElement->categories->category as $category ) $categories[] = (string) $category;
            $movie->addVendorCategory( $categories, $this->vendor->id );

            // Add First Image Only
            $medias = array();
            foreach( $movieElement->medias->media as $media ) $medias[] = (string) $media;
            if( !empty( $medias ) ) $this->addImageHelper( $movie, $medias[0] );
            
            $this->notifyImporter( $movie );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
        }
    }
  }
}
?>
