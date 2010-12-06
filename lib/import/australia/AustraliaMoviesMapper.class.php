<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */

class australiaMoviesMapper extends australiaBaseMapper
{

  public function mapMovies()
  {
    foreach( $this->feed->film as $film )
    {
        // We should rmeove this. I think!
        if( $this->insertedMoreThanThreeMonthsAgo( $film ) )
        {
          continue;
        }

        try
        {
            // Get Existing movie or Create NEW
            $vendor_movie_id = (string) $film->EventID; // Sydney sending movie like Event! and sometime events in movies, which should be Notified to vendor!
            $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $vendor_movie_id );
            if( $movie === false )
            {
                $movie = new Movie();
            }

            // Map data
            $movie['Vendor']            = $this->vendor;
            $movie['vendor_movie_id']   = $vendor_movie_id;
            $movie['name']              = (string) $film->Name;
            //$movie['plot']              = (string) $film->Description;
            //$movie['tag_line']          = (string);
            $movie['review']            = (string) $film->Description;
            $movie['url']               = (string) $film->Website;
            $movie['director']          = (string) $film->FilmDirector;
            //$movie['writer']            = (string);
            $movie['cast']              = (string) $film->FilmActors;
            $movie['age_rating']        = (string) $film->FilmOflcRating;
            //$movie['release_date']      = (string) $film->FilmYear;
            $movie['duration']          = (string) $film->FilmLength;
            $movie['country']           = (string) $film->FilmCountry;
            //$movie['language']          = (string);
            //$movie['aspect_ratio']      = (string);
            //$movie['sound_mix']         = (string);
            //$movie['company']           = (string);
            //$movie['rating']            = (string);
            $movie['utf_offset']        = (string) $this->vendor->getUtcOffset();

            //#753 addImageHelper capture Exception and notify, this don't break the Import process
            $this->addImageHelper( $movie, (string) $film->ImagePath );

            // Save
            $this->notifyImporter( $movie );
            
        } catch ( Exception $ex ) {
            
            $this->notifyImporterOfFailure( $ex, isset($movie) ? $movie : null );
            
        }
    }
  }

  private function insertedMoreThanThreeMonthsAgo( SimpleXMLElement $film )
  {
    $limit = new DateTime( 'now' );
    $limit->sub( new DateInterval( 'P3M' ) );

    $dateString = (string) $film->DateInserted;
    // swap 29/03/2010 9:59:00 AM  to   03/29/2010 9:59:00 AM
    $dateString = preg_replace( '/([0-9]{2})\/([0-9]{2})\/([0-9]{4} [0-9]+\:[0-9]{2}\:[0-9]{2} [AMP]{2})/', '$2/$1/$3', $dateString );
    $insertDate = new DateTime( $dateString );

    return $insertDate->getTimestamp() < $limit->getTimeStamp();
  }

}

?>
