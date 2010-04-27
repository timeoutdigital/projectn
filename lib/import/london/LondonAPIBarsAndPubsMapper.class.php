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
   * @param LondonAPICrawler $apiCrawler
   * @param geoEncode $geoEncoder
   */
  public function __construct( LondonAPICrawler $apiCrawler=null, geoEncode $geoEncoder=null )
  {
    parent::__construct($apiCrawler, $geoEncoder);
    $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'bar-pub' );
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
    return 'http://api.timeout.com/v1/getBar.xml';
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
    return 'Bars & Pubs';
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
      $poi->addProperty( (string) $detail['name'], (string) $detail );
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
