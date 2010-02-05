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

      $movie['vendor_id']  = $this->vendor[ 'id' ];
      $movie['utf_offset'] = $this->getUtcOffset( date( 'Y-m-d' ) );
      $movie['poi_id']     = $this->getPoiId( $filmElement );
      //$movie['rating']     = $this->extractRating( $filmElement );
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

  /**
   * find the Poi id for the placeid field in xml
   */
  private function getPoiId( $filmElement )
  {
    $vendorPoiId = (string) $filmElement->cinemaplacelink[ 'placeid' ];
    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( $vendorPoiId );
    if( $poi['id'] )
      return $poi['id'];
    else
      return 1;
  }
}
?>
