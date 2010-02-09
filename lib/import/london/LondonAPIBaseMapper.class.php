<?php
/**
 * 
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
abstract class LondonAPIBaseMapper extends DataMapper
{

  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var string
   */
  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var curlImporter
   */
  protected $curl;

  /**
   * @var geoEncode
   */
  protected $geoEncoder;

  /**
   * @var int
   */
  protected $limit = 0;
  
  /**
   * @param string $url
   * @param geoEncode $geoEncoder
   */
  public function  __construct( geoEncode $geoEncoder = null )
  {
    $this->vendor = Doctrine::getTable('Vendor')
      ->findOneByCityAndLanguage( 'london', 'en-GB' );

    $this->geoEncoder = $geoEncoder;
    $this->curl = new curlImporter();

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
  
  /**
   * Check $count against $this->limit
   *
   * @param int $count
   * @return boolean
   */
  protected function inLimit( $count )
  {
    if( $this->limit <= 0 )
      return true;

    return ( $count < $this->limit );
  }

  /**
   * Calls the London API. Pass in params using an array:
   * See London API document written by Rhodri Davis (Word file)
   *
   * <code>
   * $xml = $this->callApiSearch( array( 'type'   => 'restaurants',
   *                                     'offset' => 45 ) );
   * </code>
   *
   * @param array $params
   * @return SimpleXMLElement
   */
  protected function callApiSearch( $params )
  {
    $this->curl->pullXml( $this->searchUrl, '', $params );
    return $this->curl->getXml();
  }

  /**
   * Calls London API's getRestaurant using $uid
   * See London API document written by Rhodri Davis (Word file)
   *
   * @param string $uid
   * @return SimpleXMLElement
   */
  protected function callApiGetDetails( $uid )
  {
    $this->curl->pullXml( $this->getDetailsUrl(), '', array( 'uid' => $uid ) );
    return $this->curl->getXML();
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
  abstract protected function getDetailsUrl();
}
?>
