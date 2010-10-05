<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class Curl
{

  /**
   * @var string
   */
  private $_url;

  /**
   * @var array
   */
  private $_parameters;

  /**
   * @var string
   */
  private $_response;

  /**
   * @var string
   */
  private $_requestMethod;

  /**
   * @var string
   */
  private $_requestUrl;

  /**
   * @var string
   */
  private $_storePath;

  /**
   * @var string
   */
  private $_curlInfo;

  /**
   * @var string
   */
  private $_header;

  /**
   * @var curl handle
   */
  private $_curlHandle;

  /**
   * @var string
   */
  private $_headerString;

  /**
   * @var string
   */
  private $_options = array();

  /**
   *
   * @param string $url
   *
   * @param array $parameters takes an associative array e.g. array( 'id' => 89 )
   *
   * @param string $requestMethod
   *
   * @param boolean $returnHeaders
   *
   * @param boolean $locationFollow
   */
  public function __construct( $url,
                               $parameters     = array(),
                               $requestMethod  = 'GET' )
  {
    $this->_url           = $url;
    $this->_parameters    = $parameters;
    $this->_requestMethod = $requestMethod;
  }


  /**
   * executes the curl request
   */
  public function exec()
  {
    $curlHandle = curl_init();

    $this->setCurlDefaultOptions( $curlHandle );

    $this->_tmpHeaderFile = tmpfile();
    $this->setCurlOption( CURLOPT_WRITEHEADER, $this->_tmpHeaderFile );

    foreach( $this->_options as $key=>$value )
        curl_setopt( $curlHandle, $key, $value );

    $this->_response= curl_exec( $curlHandle );
    $this->_curlInfo = curl_getinfo( $curlHandle );

    curl_close( $curlHandle );

    fseek( $this->_tmpHeaderFile, 0);
    //get rid of charriage return character (ascii 13) as they mess up the further processing
    $this->_headerString = str_replace( chr(13), '', fread( $this->_tmpHeaderFile, 1024 ) ) ;

    fclose( $this->_tmpHeaderFile ); // Need to close this!

    if ( !isset( $this->_curlInfo[ 'http_code' ] ) || !in_array( $this->_curlInfo[ 'http_code' ], array( '200', '304' ) ) )
    {
        throw new Exception( 'Curl Error, failed to fetch content (no http_code 200 or 304 received, got ' . $this->_curlInfo['http_code'] . ') for ' . $this->_requestUrl );
    }
  }

  /**
   *
   * @param string $option
   * @param mixed $value
   * @return boolean
   */
  public function setCurlOption( $option, $value )
  {
    $this->_options[ $option ] = $value;
  }

  /**
   * returns the curl information
   *
   * @return array
   */
  public function getCurlInfo()
  {
      return $this->_curlInfo;
  }
  
  private function setCurlDefaultOptions( $curlHandle )
  {
    $url = $this->getUrl();

    if( $this->_requestMethod == 'GET' )
    {
      $paramString = $this->getParametersString();
      $url .= ( empty( $paramString ) ? '' : '?' . $paramString );
      curl_setopt( $curlHandle, CURLOPT_HTTPGET, true );
    }
    else
    {
      curl_setopt( $curlHandle, CURLOPT_POST, true );
      curl_setopt($curlHandle,CURLOPT_POSTFIELDS, $this->_parameters );
    }
    
    curl_setopt( $curlHandle, CURLOPT_URL, $url );
    $this->_requestUrl = $url;

    curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $curlHandle, CURLOPT_USERAGENT, "Mozilla/4.0" );
  }

  /**
   * Return the result of the curl call
   *
   * @return string
   */
  public function getResponse()
  {
    return $this->_response;
  }

  /**
   * Stores the result of a curl call in a file
   *
   * @param string $filepath
   * @return boolean
   */
  public function storeResponse( $filepath )
  {
    if ( trim( $filepath ) == '' )
    {
        throw new Exception( 'Curl Error, empty filename passed to storeResponse()' );
    }

    if ( $this->_response === NULL )
    {
        throw new Exception( 'Curl Error, no response to write in storeResponse()' );
    }

    if ( isset( $this->_curlInfo[ 'http_code' ] ) && $this->_curlInfo[ 'http_code' ] == '200' )
    {
      
        $pathArray = explode( '/', $filepath );

        if ( 1 < count( $pathArray ) )
        {
            $file = array_pop( $pathArray );
            $path = implode( '/', $pathArray );
            $this->setStorePath( $path );
        }
        else
        {
            $file = $pathArray[ 0 ];
        }

        if ( $this->_storePath === NULL )
        {
            throw new Exception( 'Curl Error, no file path specified for  storeResponse()' );
        }

        //is int used to get proper boolean return value
        return is_int( file_put_contents( $this->_storePath . '/' . $file, $this->_response ) );
    }
    else
    {
        return false;
    }
  }

  /**
   * Sets the store path for saveResponse, etc.
   *
   * @param string $path
   */
  public function setStorePath( $path )
  {
    $this->_storePath = rtrim( $path, '/' );

    if( ! file_exists( $this->_storePath ) )
    {
      if ( ! mkdir( $this->_storePath, 0777, true ) )
      {
          throw new Exception( 'Curl Error, could not create store path' );
      }
    }
  }

  /**
   * returns the store path
   *
   * @param string $path
   */
  public function getStorePath()
  {
    return $this->_storePath;
  }

  /**
   * Return the URL
   *
   * @return string
   */
  public function getUrl()
  {
    return $this->_url;
  }

  /**
   * Return the parameters
   *
   * @return array
   */
  public function getParameters()
  {
    return $this->_parameters;
  }

  /**
   * Return the parameters URL-encoded query string
   *
   * @return string
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
    return $this->_requestUrl;
  }

  /**
   * Return the request method
   *
   * @return string
   */
  public function getRequestMethod()
  {
    return $this->_requestMethod;
  }

  /**
   * Whether following Location headers is set to true
   *
   * @return boolean
   */
  public function getLocationFollow()
  {
    return $this->_locationFollow;
  }

  /**
   * returns header
   *
   * @return string
   */
  public function getHeader()
  {
    return $this->_headerString;

  }

  /**
   * greps out a particular field of the header
   *
   * @param string $field
   * @return string
   */
  public function getHeaderField( $field )
  {
      $matches = array();

      // Used preg_match_all to get Re-directed header information as well..
      preg_match_all( '/' . preg_quote( $field ) . '\:\s(.*)/',  $this->getHeader(), $matches );

      // When redirected, we will have second level as ARRAY(),
      // This statement will get the Last Redirected Header Information
      $lastMatch = ( is_array( $matches[0] ) ) ? array_pop( $matches ) : $matches;

      // Matche should be in an Array
      if ( is_array( $lastMatch ) )
      {
        return array_pop( $lastMatch ); // Return the Last value in the Array
      }

      return '';
  }

  /**
   * returns the date included in the header of the request (if available)
   *
   * @return string
   */
  public function getDate()
  {
    return $this->getHeaderField( 'Date' );
  }

  /**
   * returns the server information included in the header of the request (if available)
   *
   * @return string
   */
  public function getServer()
  {
    return $this->getHeaderField( 'Server' );
  }

  /**
   * returns the last modified date included in the header of the request (if available)
   *
   * @return string
   */
  public function getLastModified()
  {
    return $this->getHeaderField( 'Last-Modified' );
  }

  /**
   * returns the ETag included in the header of the request (if available)
   *
   * @return string
   */
  public function getETag()
  {
    return $this->getHeaderField( 'ETag' );
  }

  /**
   * returns the content-length included in the header of the request (if available)
   *
   * @return string
   */
  public function getContentLength()
  {
    return $this->getHeaderField( 'Content-Length' );
  }

  /**
   * returns the content-type included in the header of the request (if available)
   *
   * @return string
   */
  public function getContentType()
  {
    return $this->getHeaderField( 'Content-Type' );
  }

  /**
   * downlaods a a file to the specified location, if $lastModified date
   * is passed it will only download the file if the lastModified date
   * is newer than the specified date
   *
   * @param string $filepath
   * @param string $lastModified
   */
  public function downloadTo( $filepath, $lastModified = false )
  {
      if ( $lastModified !== false )
      {
          $lastModifiedInSec = strtotime( $lastModified );
      }

      //needs to be checked for false again as strtotime above could return
      //false too
      if ( $lastModified !== false )
      {
          $this->setCurlOption( CURLOPT_TIMECONDITION, true);
          $this->setCurlOption( CURLOPT_TIMEVALUE, $lastModifiedInSec );
      }

      $this->exec();
      $this->storeResponse( $filepath );
  }

  /**
   * Get the last set of headers, removing redirect headers.
   */
  public static function fetchAuthoritativeHeader( $url, $headerArray = array() /* Optionally provide your own headers */ )
  {
      $headers = !empty( $headerArray ) ? $headerArray : get_headers( $url, 1 );
      if( $headers === false ) return array();

      foreach( $headers as $key => $value )
      {
          if( is_array( $value ) )
          {
            $headers[ $key ] = array_pop( $value );
          }
          if( is_numeric( $key ) )
          {
            preg_match( "/\s([0-9]{3})\s/", $value, $matches );
            $headers[ 'Status-Code' ] = array_pop( $matches );
            unset( $headers[ $key ] );
          }
      }
      return $headers;
  }

}
?>
