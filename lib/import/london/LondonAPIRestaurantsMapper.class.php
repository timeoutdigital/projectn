<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage london.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LondonAPIRestaurantsMapper extends LondonAPIBaseMapper
{
  /**
   * @var PoiCategory
   */
  private $poiCategory;

  /**
   *
   */
  public function  __construct( LondonAPICrawler $apiCrawler, geoEncode $encoder=null  )
  {
    parent::__construct( $apiCrawler, $geoEncoder );
    $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'restaurant' );
  }

  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $this->crawlApi();
  }
  
  /**
   * Returns the London API URL
   * 
   * @return string
   */
  public function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getRestaurant.xml';
  }

  /**
   * Returns the API type
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  public function getApiType()
  {
    return 'Restaurants';
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $restaurantXml
   */
  public function doMapping( SimpleXMLElement $restaurantXml )
  {
    $poi = new Poi();
    $this->mapCommonPoiMappings($poi, $restaurantXml);
    
    $poi[ 'star_rating' ] = (int) $restaurantXml->starRating;
    $poi[ 'PoiCategories' ][] = $this->poiCategory;

    foreach( $restaurantXml->details as $detail )
    {
      $poi->addProperty( (string) $detail['name'], (string) $detail );
    }

    $this->notifyImporter( $poi );
  }
}
?>
