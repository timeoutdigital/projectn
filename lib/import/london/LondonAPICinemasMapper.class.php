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
   * @param LondonAPICrawler $apiCrawler
   * @param geocoder $geocoderr
   */
  public function __construct( LondonAPICrawler $apiCrawler=null, geocoder $geocoderr=null )
  {
    parent::__construct($apiCrawler, $geocoderr);
    $this->poiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'cinema' );
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
    return 'http://api.timeout.com/v1/getCinema.xml';
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
    return 'Cinemas';
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
