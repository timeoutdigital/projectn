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

class sydneyFtpMoviesMapper extends DataMapper
{
  /**
   * @var SimpleXMLElement
   */
  private $feed;

  /**
   * @var projectnDataMapperHelper
   */
  private $dataMapperHelper;

  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @param SimpleXMLElement $feed
   */

  public function __construct( Vendor $vendor, SimpleXMLElement $feed )
  {
    $this->feed = $feed;
    $this->vendor = $vendor;
    $this->dataMapperHelper = new projectnDataMapperHelper( $vendor );
  }

  public function mapMovies()
  {
    foreach( $this->feed->film as $film )
    {
        if( $this->insertedMoreThanThreeMonthsAgo( $film ) )
        {
          continue;
        }
        $movie = $this->dataMapperHelper->getMovieRecord( (string) $film->EventID );
        $movie['Vendor']            = $this->vendor;
        $movie['vendor_movie_id']   = (string) $film->EventID;
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

        try
        {
            $movie->addMediaByUrl( (string) $film->ImagePath );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure($exception);
        }


        ImportLogger::saveRecordComputeChangesAndLog( $movie );
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
