<?php
/**
 * London API Bars and Pubs Mapper
 *
 * @package projectn
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LondonAPIBarsAndPubsMapper extends LondonAPIBaseMapper
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
  public function __construct( Doctrine_Record $vendor, $params )
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
      $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'bar-pub' );

      // Call Parent to do something
      parent::__construct( $vendor, $params );
  }

  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $this->apiCrawler->crawlApi();
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
   * Map $barsXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $barsXml
   */
  public function doMapping( SimpleXMLElement $barsXml )
  {
    $poi = $this->dataMapperHelper->getPoiRecord((string) $barsXml->uid);

    try
    {
      $this->mapCommonPoiMappings($poi, $barsXml);
    }
    catch( Exception $exception )
    {
      $this->notifyImporterOfFailure($exception, $poi);
      return;
    }
    $poi['PoiCategory'][]   = $this->poiCategory;
    $poi['star_rating']       = (int) $barsXml->starRating;

    foreach( $this->getDetails( $barsXml ) as $detail )
    {
      $this->addDetailAsProperty( $poi, $detail );
    }

    if( !empty( $barsXml->imageUrl ) )
    {
        $notResizedImageUrl = $this->rewriteMediaUrlToRemoveScaling( $barsXml->imageUrl );

        if( $notResizedImageUrl !== false /* Don't add Image if the URL has errors */ )
        {
            $this->addImageHelper( $poi, $notResizedImageUrl );
        }
    }

    $this->notifyImporter( $poi );
  }
}
?>
