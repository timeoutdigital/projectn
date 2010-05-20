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

class kualaLumpurMoviesMapper extends DataMapper
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
    foreach( $this->feed->eventDetails as $film )
    {
        try {
            $movie = $this->dataMapperHelper->getMovieRecord( (string) $film->id );
            $movie['Vendor']            = $this->vendor;
            $movie['vendor_movie_id']   = (string) $film->id;
            $movie['name']              = (string) $film->title;
            //$movie['plot']              = (string) $film->Description;
            //$movie['tag_line']          = (string);
            $movie['review']            = (string) $film->descripton;
            $movie['url']               = (string) $film->url;
            //$movie['director']          = (string);
            //$movie['writer']            = (string);
            //$movie['cast']              = (string);
            //$movie['age_rating']        = (string);
            //$movie['release_date']      = (string);
            //$movie['duration']          = (string);
            //$movie['country']           = (string);
            //$movie['language']          = (string);
            //$movie['aspect_ratio']      = (string);
            //$movie['sound_mix']         = (string);
            //$movie['company']           = (string);
            //$movie['rating']            = (string);
            $movie['utf_offset']        = (string) $this->vendor->getUtcOffset();

            try {
                $movie->addMediaByUrl( (string) $film->medias->big_image );
            }
            catch( Exception $exception )
            {
                $this->notifyImporterOfFailure($exception);
            }

            $this->notifyImporter( $movie );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure($exception, $movie);
        }
    }
  }

}

?>
