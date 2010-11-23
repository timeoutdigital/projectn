<?php
/**
 * London API Cinemas Mapper
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
class LondonAPICinemasMapper extends LondonAPIBaseMapper
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
   * @param LondonAPICrawler $apiCrawler
   * @param geocoder $geocoderr
   */
  public function __construct(  Doctrine_Record $vendor, $params )
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
      $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'cinema' );

      // Call Parent to do something
      parent::__construct( $vendor, $params );
  }

  /**
   * Map cinemas data to Poi and notify the Importer as each Poi is mapped
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
   * @param SimpleXMLElement $cinemaXml
   */
  public function doMapping( SimpleXMLElement $cinemaXml )
  {
      $poi = $this->dataMapperHelper->getPoiRecord( (string) $cinemaXml->uid );

    try
    {
        $this->mapCommonPoiMappings($poi, $cinemaXml);
    }
    catch( Exception $exception )
    {
        $this->notifyImporterOfFailure($exception, $poi);
        return;
    }

    //$poi['PoiCategory'][]   = $this->poiCategory;
    $poi['star_rating']       = (int) $cinemaXml->starRating;

    //@todo add userRating

    $this->notifyImporter( $poi );
  }
}
?>
