<?php
/**
 * Description
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
class LisbonFeedMoviesMapper extends LisbonFeedBaseMapper
{
  public function mapMovies()
  {
    $lastTitle = '';

    foreach( $this->xml->films as $filmElement )
    {
      if( (string) $filmElement['title'] == $lastTitle )
      {
        continue;
      }
      $lastTitle = (string) $filmElement[ 'title' ];

      $movie = new Movie();
      
      $this->mapAvailableData($movie, $filmElement, 'MovieProperty' );

      $movie['vendor_id'] = $this->vendor[ 'id' ];
      $movie['utf_offset'] = $this->getUtcOffset( date( 'Y-m-d' ) );
      $movie['poi_id'] = $filmElement->cinemaplacelink[ 'placeid' ];
      $movie['rating'] = $this->extractRating( $filmElement );
      $movie['age_rating'] = '';

      $this->notifyImporter( $movie );
      $movie->free();
    }
  }

  private function extractRating( $filmElement )
  {
    $ratingToUse = $filmElement[ 'ReviewToUse' ];
    return (string) $filmElement[ 'Review' . $ratingToUse . 'rating' ];
  }

  /**
   * Return an array of mappings from xml attributes to record fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array(
        'filmID' => 'vendor_movie_id',
        'title' => 'name',
    );
  }

  /**
   * Return an array of attributes to ignore when mapping
   *
   * @return array
   */
  protected function getIgnoreMap()
  {
    return array();
  }
}
?>
