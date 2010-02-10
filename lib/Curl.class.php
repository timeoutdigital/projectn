<?php
/**
 * Description
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
class Curl
{
  const REQUEST_USING_GET      = 'GET';
  const REQUEST_USING_POST     = 'POST';

  const RETURN_WITH_HEADERS    = true;
  const RETURN_WITHOUT_HEADERS = false;

  const LOCATION_FOLLOW       = true;
  const LOCATION_DONT_FOLLOW  = false;

  /**
   * @var $url
   */
  private $url;

  /**
   * @var $parameters
   */
  private $parameters;

  /**
   * @var $response
   */
  private $response;

  /**
   * @var $requestMethod
   */
  private $requestMethod;

  /**
   * @var $requestUrl
   */
  private $requestUrl;

  /**
   * @var $returnHeaders;
   */
  private $returnHeaders;

  /**
   * @var $locationFollow
   */
  private $locationFollow;

  /**
   *
   * @param string $url
   *
   * @param array $parameters takes an associative array e.g. array( 'id' => 89 )
   *
   * @param string $requestMethod Curl::REQUEST_USING_GET | CURL::REQUEST_USING_POST
   *
   * @param boolean $returnHeaders Curl::RETURN_WITH_HEADERS | Curl::RETURN_WITHOUT_HEADERS
   *
   * @param boolean $locationFollow Curl::LOCATION_FOLLOW | Curl::LOCATION_DONT_FOLLOW
   */
  public function __construct( $url,
                                $parameters     = array(),
                                $requestMethod  = Curl::REQUEST_USING_GET,
                                $returnHeaders  = Curl::RETURN_WITHOUT_HEADERS,
                                $locationFollow = Curl::LOCATION_FOLLOW )
  {
    $this->url = $url;
    $this->parameters = $parameters;
    $this->requestMethod = $requestMethod;
    $this->returnHeaders = $returnHeaders;
    
    $curlHandle = curl_init();

    $this->setCurlOptions($curlHandle);

    $this->response = curl_exec($curlHandle);
    
    curl_close($curlHandle);
  }
  
  private function setCurlOptions( $curlHandle )
  {
    $url = $this->getUrl();

    if( $this->requestMethod == Curl::REQUEST_USING_GET )
    {
      $url .= '?' . $this->getParametersString();
      curl_setopt( $curlHandle, CURLOPT_HTTPGET, true );
    }
    else
    {
      curl_setopt( $curlHandle, CURLOPT_POSTFIELDS, $this->getQueryString() );
      curl_setopt( $curlHandle, CURLOPT_POST, true );
    }
    
    curl_setopt( $curlHandle, CURLOPT_URL, $url );
    $this->requestUrl = $url;

    curl_setopt( $curlHandle, CURLOPT_HEADER, $this->returnHeaders );
    curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, $this->locationFollow );
    curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
  }

  /**
   * Return the result of the curl call
   *
   * @return string
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Return the URL
   *
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }

  /**
   * Return the parameters
   *
   * @return array
   */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Return the parameters URL-encoded query string
   *
   * @return boolean
   */
  public function getParametersString()
  {
    return http_build_query( $this->getParameters() );
  }

  /**
   * Return the url called by curl
   *
   * @return string
   */
  public function getRequestUrl()
  {
    return $this->requestUrl;
  }

  /**
   * Return the request method
   *
   * @return string
   */
  public function getRequestMethod()
  {
    return $this->requestMethod;
  }

  /**
   * Whether return headers is set to true
   *
   * @return boolean
   */
  public function getReturnHeaders()
  {
    return $this->returnHeaders;
  }

  /**
   * Whether following Location headers is set to true
   *
   * @return boolean
   */
  public function getLocationFollow()
  {
    return $this->locationFollow;
  }
}
?>
