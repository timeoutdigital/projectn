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

  /**
   * @var string
   */
  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var string
   */
  protected $city = 'London';

  /**
   * @var string
   */
  protected $country = 'GBR';

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
   * @param string $type
   */
  protected function crawlApi()
  {
    $type = $this->getApiType();

    $searchXml = $this->callApiSearch( array( 'type' => $type, 'offset' => 0 ) );

    $numPerPage = $searchXml->responseHeader->rows;
    $numResults = $searchXml->responseHeader->numFound;

    $numResultsMapped = 0;

    for( $offset = 0; $offset < $numResults; $offset += $numPerPage )
    {
      $searchPageXml = $this->callApiSearch( array( 'type' => $type, 'offset' => $offset ) );

      foreach( $searchPageXml->response->block->row as $row )
      {
        $xml = $this->callApiGetDetails( $row->uid );

        $this->doMapping( $xml );
        if( !$this->inLimit( ++$numResultsMapped ) ) return;
      }
    }
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
   * Calls London API's get<type> using $uid and returns the <row> node. This
   * node contains the information we are interested in
   * 
   * See London API document written by Rhodri Davis (Word file)
   * 
   * @todo need to create fixtures to test for Exception when API returns
   * xml without a <rpw> node.
   *
   * @param string $uid
   * @return SimpleXMLElement
   */
  protected function callApiGetDetails( $uid )
  {
    $this->curl->pullXml( $this->getDetailsUrl(), '', array( 'uid' => $uid ) );
    $xml = $this->curl->getXML();

    if( !$xml )
    {
      throw new Exception( 'API call "'. $this->getDetailsUrl() .'?uid="' . $uid . '" returned nothing.' );
    }

    $nodeContainingDetails = $xml->response->row;
    return $nodeContainingDetails;
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

  /**
   * Return the API type
   * e.g. Restaurants, Bar & Pubs, Cinemas ...
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  abstract protected function getApiType();

  /**
   * Do mapping of xml to poi and notify Importer here
   */
  abstract protected function doMapping( SimpleXMLElement $xml );
}
?>
