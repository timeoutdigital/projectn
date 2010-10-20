<?php
/**
 * London API Crawler
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
class LondonAPICrawler
{
  /**
   * @var string
   */
  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * The API url for an instance of a type
   *
   * e.g. the feed for a Restaurant
   *
   * @var string
   */
  protected $detailsUrl;

  /**
   * Types are available on http://api.timeout.com/v1/getTypes.xml
   *
   * @var string
   */
  protected $apiType;

  /**
   *
   * @var LondonAPIBaseMapper
   */
  protected $mapper;

  /**
   * @var int
   */
  protected $limit = 0;

  /**
   * @var curlImporter
   */
  protected $curl;

  /**
   *protected
   * @param string $detailsUrl The API url for an instance of a type,
   * e.g. http://api.timeout.com/v1/getBars.xml
   *
   * @param string $type see http://api.timeout.com/v1/getTypes.xml
   */
  public function  __construct()
  {
    $this->curl = new curlImporter();
  }

  /**
   *
   * @param LondonAPIBaseMapper $mapper
   */
  public function setMapper( $mapper )
  {
    $this->mapper = $mapper;
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
  public function crawlApi()
  {
    $type = $this->mapper->getApiType();

    try
    {
      $searchXml = $this->callApiSearch( array( 'type' => $type, 'offset' => 0 ) );
    }
    catch( Exception $exception )
    {
      $this->mapper->onException($exception, 'Call to API search failed.');
    }

    $numPerPage = $searchXml->responseHeader->rows;
    $numResults = $searchXml->responseHeader->numFound;

    $numResultsMapped = 0;

    for( $offset = 0; $offset < $numResults; $offset += $numPerPage )
    {
      try
      {
        $searchPageXml = $this->callApiSearch( array( 'type' => $type, 'offset' => $offset ) );
      }
      catch( Exception $exception )
      {
        $this->mapper->onException($exception);
      }

      foreach( $searchPageXml->response->block->row as $row )
      {
        try
        {
          $xml = $this->callApiGetDetails( $row->uid );
        }
        catch( Exception $exception )
        {
          $this->mapper->onException($exception);
        }

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

    // Archive API Response
    $tempVendor = new Vendor();
    $tempVendor[ 'city' ] = 'london';
    new FeedArchiver( $tempVendor, $this->curl->getResponse(), $params['type'] );
    unset( $tempVendor );

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
      throw new Exception( 'API call "'. $this->getDetailsUrl() .'?uid=' . $uid . '" returned nothing.' );
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
  protected function getDetailsUrl()
  {
    return $this->mapper->getDetailsUrl();
  }

  /**
   * Return the API type
   * e.g. Restaurants, Bar & Pubs, Cinemas ...
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  protected function getApiType()
  {
    return $this->mapper->getApiType();
  }

  /**
   * Do mapping of xml to poi and notify Importer here
   */
  protected function doMapping( SimpleXMLElement $xml )
  {
    $this->mapper->doMapping( $xml );
  }
}
?>
