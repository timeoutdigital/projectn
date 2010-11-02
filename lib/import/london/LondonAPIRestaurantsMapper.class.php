<?php
/**
 * London API Restaurants Mapper
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
   * @var string Hold the url to Get Detailed information
   */
  private $detailedURL;

  /**
   *
   * @var string Query Type for Details
   */
  private $apiType;
  
  /**
   *
   */
  public function  __construct(  Doctrine_Record $vendor, $params  )
  {
      if( !is_array( $params ) || !isset($params['datasource']) || !is_array( $params['datasource'] ))
      {
          throw new Exception ( 'Invalid Parameter' );
      }
      
      // Set Params Data
      $this->apiCrawler     = new $params[ 'datasource' ]['classname']();
      $this->searchUrl      = $params[ 'datasource' ]['url'];
      $this->detailedURL    = $params[ 'datasource' ]['detailedurl'];
      $this->apiType        = $params[ 'datasource' ]['apitype'];

      // Set Default fallback Category
      $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'restaurant' );

      // Call Parent to do something
      parent::__construct( $vendor, $params );
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
      return $this->detailedURL;
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
      return $this->apiType;
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $restaurantXml
   */
  public function doMapping( SimpleXMLElement $restaurantXml )
  {
    $poi = $this->dataMapperHelper->getPoiRecord( (string) $restaurantXml->uid );
    try
    {
      $this->mapCommonPoiMappings($poi, $restaurantXml);
    }
    catch( Exception $exception )
    {
      $this->notifyImporterOfFailure($exception, $poi);
      return;
    }
    
    $poi[ 'star_rating' ] = (int) $restaurantXml->starRating;
    $poi[ 'PoiCategory' ][] = $this->poiCategory;

    if( !empty( $restaurantXml->imageUrl ) )
    {
        $notResizedImageUrl = $this->rewriteMediaUrlToRemoveScaling( $restaurantXml->imageUrl );

        if( $notResizedImageUrl !== false /* Don't add Image if the URL has errors */ )
        {
            $this->addImageHelper( $poi, $notResizedImageUrl );
        }
    }

    foreach( $this->getDetails( $restaurantXml ) as $detail )
    {
      $this->addDetailAsProperty( $poi, $detail );
    }

    $this->notifyImporter( $poi );
  }
}
?>
