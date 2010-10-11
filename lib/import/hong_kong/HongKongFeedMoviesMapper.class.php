<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class HongKongFeedMoviesMapper extends HongKongFeedBaseMapper
{
  public function mapMovies()
  {

    foreach( $this->fixIteration( $this->xml->channel->movies->movie ) as $movieElement )
    {
        try {
            // Set Vendor Unknown For Import Logger
            ImportLogger::getInstance()->setVendorUnknown();

            // Get Movie Id
            $vendor_movie_id = (int) $movieElement['id'];

            $movie = Doctrine::getTable("Movie")->findByVendorMovieIdAndVendorLanguage( $vendor_movie_id, 'en-HK' );
            if( !$movie ) $movie = new Movie();

            // Column Mapping
            $movie['Vendor']            = $this->vendor; // Get Vendor from Super class
            $movie['vendor_movie_id']   = $vendor_movie_id;
            $movie['name']              = (string) $movieElement->name;
            
            $movie['review']            = $this->fixHtmlEntities( (string) $movieElement->review ); // Requires Double Entity Decoding
            $movie['url']               = (string) $movieElement->url;
            $movie['rating']            = $this->roundNumberOrReturnNull( (string) $movieElement->rating );

            // Timeout Link
            if( (string) $movieElement->timeout_url != "" )
                $movie->addProperty( "Timeout_link", (string) (string) $movieElement->timeout_url );

            // Booking Url
            if( (string) $movieElement->booking_url != "" )
                $movie->addProperty( "Booking_url" , (string) $movieElement->booking_url );
            
            // Add Short Description into Property
            $short_description = $this->fixHtmlEntities( (string) $movieElement->short_description );   // Requires Double Entity Decoding
            if( $short_description != "" )
                $movie->addProperty ("Short_description", $short_description);

            // UTC Offset (Requires Vendor)
            $movie['utf_offset']        = (string) $movie['Vendor']->getUtcOffset();

            // Add Medias to movie
            if( isset( $movieElement->medias->media ) )
            {
                $processed_medias = array();
                foreach( $movieElement->medias->media as $media )
                {
                    $media_url = (string) $media->url;
                    if( !in_array( $media_url, $processed_medias ) )
                        $this->addImageHelper( $movie, $media_url );
                    $processed_medias[] = $media_url;
                }
            }
            // Import to DB
            $this->notifyImporter( $movie );

            unset( $movie ); // Done & Delete Movie Data
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, isset( $movie ) ? $movie : null );
            print_r( $exception->getMessage() );
        }
    }
  }
}
?>
