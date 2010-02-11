<?php
/**
 * 
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
abstract class LondonAPIBaseMapper extends DataMapper
{

  /**
   * @var Vendor
   */
  protected $vendor;
//
//  /**
//   * @var string
//   */
//  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var string
   */
  protected $city = 'London';

  /**
   * @var string
   */
  protected $country = 'GBR';

  /**
   * @var geoEncode
   */
  protected $geoEncoder;

  /**
   * @var int
   */
  protected $limit = 0;

  /**
   * @var LondonAPICrawler
   */
  protected $apiCrawler;
  
  /**
   * @param LondonAPICrawler $apiCrawler
   * @param geoEncode $geoEncoder
   */
  public function  __construct( LondonAPICrawler $apiCrawler=null, geoEncode $geoEncoder = null )
  {
    $this->vendor = Doctrine::getTable('Vendor')
      ->findOneByCityAndLanguage( 'london', 'en-GB' );

    if( is_null( $apiCrawler ) )
    {
      $apiCrawler = new LondonAPICrawler();
    }

    $apiCrawler->setMapper( $this );
    $this->apiCrawler = $apiCrawler;
    $this->geoEncoder = $geoEncoder;

    if( is_null( $geoEncoder ) )
    {
      $this->geoEncoder = new geoEncode();
    }
  }
  
  /**
   * Limit the number of results to map
   * Set to zero (0) for no limit
   *
   * @param int $limit
   */
  public function setLimit( $limit )
  {
    $this->limit = $limit;
  }

  /**
   * Get the current result limit
   *
   * @return int
   */
  public function getLimit()
  {
    return $this->limit;
  }

  protected function crawlApi()
  {
    $this->apiCrawler->crawlApi();
  }

  /**
   * Return the URL for get the details of an API result row.
   *
   * For example, restaurant subclass would be implemented as:
   *
   * <code>
   * protected function getDetailsUrl()
   * {
   *   return 'http://api.timeout.com/v1/getRestaurant.xml'
   * }
   * </code>
   *
   * @returns string
   */
  abstract public function getDetailsUrl();

  /**
   * Return the API type
   * e.g. Restaurants, Bar & Pubs, Cinemas ...
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  abstract public function getApiType();

  /**
   * Do mapping of xml to poi and notify Importer here
   */
  abstract public function doMapping( SimpleXMLElement $xml );
}
?>
