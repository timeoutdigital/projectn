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
    foreach( $this->fixIteration( $this->xml->movie ) as $movieElement )
    {
        try {
            // Set Vendor Unknown For Import Logger
            ImportLogger::getInstance()->setVendorUnknown();

            // Get Movie Id
            $vendor_movie_id = (int) $movieElement['id'];

            $movie = Doctrine::getTable("Movie")->findByVendorMovieIdAndVendorLanguage( $vendor_movie_id, 'ru' );
            if( !$movie ) $movie = new Movie();

            // Seperate Russian / Foreign names
            $movieName = $this->splitRussianName((string) $movieElement->name);
            
            // Column Mapping
            $movie['vendor_movie_id']   = $vendor_movie_id;
            $movie['name']              = stringTransform::mb_trim($movieName[0]); // 0 Alwasy Russian names
            $movie['plot']              = $this->fixHtmlEntities( (string) $movieElement->plot ); // Requires Double Entity Decoding
            $movie['review']            = $this->fixHtmlEntities( (string) $movieElement->review ); // Requires Double Entity Decoding
            $movie['url']               = (string) $movieElement->url;
            $movie['rating']            = $this->roundNumberOrReturnNull( (string) $movieElement->rating );

            // Booking Url
            if( (string) $movieElement->booking_url != "" )
                $movie->addProperty( "Booking_url" , (string) $movieElement->booking_url );

            // Timeout Link
            if( (string) $movieElement->timeout_url != "" )
                $movie->addProperty( "Timeout_link", (string) $movieElement->timeout_url );

            // add English Title IF not empty
            if( count($movieName) > 1 && stringTransform::mb_trim($movieName[1]) != '' )
                $movie->addProperty( "English_title", stringTransform::mb_trim($movieName[1]) );

            // Reset Cities Array
            $cities = array();

            // Attach Venues
            foreach( $movieElement->venues->venue as $xmlVenue )
            {
                try {
                    // Get Occurrence Id
                    $vendor_venue_id = (int) $xmlVenue['id'];
                    if( !isset( $vendor_venue_id ) || !is_numeric( $vendor_venue_id ) ) continue;

                    $poi = Doctrine::getTable("Poi")->findByVendorPoiIdAndVendorLanguage( $vendor_venue_id, 'ru' );
                    if( !$poi )
                    {
                        $this->notifyImporterOfFailure( new Exception( "Could not find POI for Movie with Id: " . $vendor_movie_id ), isset( $movie ) ? $movie : null );
                        continue;
                    }

                    // Set Vendor For Import Logger
                    ImportLogger::getInstance()->setVendor( $poi['Vendor'] );

                    // Only one Movie Per City
                    if( in_array( $poi['Vendor']['city'], $cities ) ) continue;

                    $movie['Vendor'] = $poi['Vendor'];
                    $cities[] = $poi['Vendor']['city'];

                    // Genres (Requires Vendor)
                    foreach( $movieElement->categories->category as $category )
                        $movie->addGenre( (string) $category, $movie['Vendor']['id'] );

                    // UTC Offset (Requires Vendor)
                    $movie['utf_offset']        = (string) $movie['Vendor']->getUtcOffset();

                    //Add Images (Requires Vendor)
                    $processed_medias = array();
                    foreach( $movieElement->medias->media as $media )
                    {
                        $media_url = (string) $media;
                        if( !in_array( $media_url, $processed_medias ) )
                            $this->addImageHelper( $movie, $media_url );
                        $processed_medias[] = $media_url;
                    }

                    $this->notifyImporter( $movie );

                    $movie = $movie->copy();
                }
                catch( Exception $exception )
                {
                    $this->notifyImporterOfFailure( $exception, isset( $movie ) ? $movie : null );
                }
            }
            unset( $movie );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, isset( $movie ) ? $movie : null );
        }
    }
  }

  /**
    * Split Movie Name by / and Return Russian names only
    * @param string $movieName
    * @return string Russian Movie name (or Null)
    */
  private function splitRussianName($movieName){
      if($movieName == null || stringTransform::mb_trim($movieName) =='' ) return null;

      $movieName = mb_split('/', $movieName); // Split

      return $movieName; // Return BOTH
  }

}
?>
