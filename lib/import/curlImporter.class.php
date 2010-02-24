<?php
/**
 * Retrieve an XML feed from a website as Simple XML
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Tim Bowler <timbowler@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * <b>Example: </b>
 * <code>
 *
 *  $xmlObj = new curlImporter
 *
 *  $parameters = array('from' => '2010-01-01', 'to' => '2010-01-30');
 *  $returnObj = $xmlObj->pullXml('http://www.timeout.pt/', 'xmllist.asp', $parameters);
 *
 * </code>
 *
 */
class curlImporter
{

  private $_curlParameters;
  private $_url;
  private $_requestMethod;
  private $_xmlRequest;
  private $_xmlResponseRaw;
  private $_simpleXml;
  
  public function  __construct()
  {
    
  }
  
  /**
   * Build the query string
   *
   * @return string The array converted to the params
   */
  private function buildCurlParamString()
  {
       
      if($this->_curlParameters != '')
      {
          $urlstring = '';

           foreach ($this->_curlParameters as $key => $value) {
               $urlstring .= urlencode($key).'='.urlencode($value).'&';
           }

           if (trim($urlstring) != '')
           {
               $urlstring = preg_replace("/&$/", "", $urlstring);
               return ($urlstring);
           }
           else
           {
               throw new Exception( 'Couldn\'t Build Parameter String' );
           }
      }
  }

  /**
   * Uses curl to access the site and fetch the xml as a string
   *
   * @return string Raw data from the page
   */
  private function curlRequest() {

       $urlstring=$this->buildCurlParamString();

       $ch=curl_init();
       
       if ( $this->_requestMethod == 'POST' )
       {
         curl_setopt($ch, CURLOPT_URL, $this->_url.$this->_xmlRequest);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $urlstring);
       }
       else
       {
            if($this->_curlParameters != ''){
                curl_setopt($ch, CURLOPT_URL, $this->_url.$this->_xmlRequest.'?'.$urlstring);
            }
            else
            {
                curl_setopt($ch, CURLOPT_URL, $this->_url.$this->_xmlRequest);
            }
       }

       curl_setopt($ch, CURLOPT_TIMEOUT, 200);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_POST, 1);
       
       $data=curl_exec($ch);
       curl_close($ch);
       
       return($data);
   }

   private function getFeed()
   {
       $rawData=$this->curlRequest();
       $this->_xmlResponseRaw = str_replace('&', '&amp;', $rawData);
   }


   /**
    * Pull the XML from a site
    *
    * @param string $url            the URL
    * @param string $request        the Script
    * @param array  $parameters     the parameters
    * @param string $requestMethod  the request method, 'GET' (default ) or 'POST'
    * @param boolean $overrideCharset
    *
    */
   public function pullXml($url, $request, $parameters='', $requestMethod = 'GET', $overrideCharset = false )
   {
     $this->_url = $url;
     $this->_xmlRequest = $request;
     $this->_curlParameters = $parameters;
     $this->_requestMethod = $requestMethod;
     $this->getFeed();
   
     /**
      * @todo refactor line 109 into this - simple xml breaks due to html & in tags that are not surrounded by cdata
      */
     libxml_use_internal_errors(true);
     $xmlString = stringTransform::stripEmptyLines( $this->_xmlResponseRaw );

     if ( $overrideCharset )
     {
         $xmlString = preg_replace( '/^' . preg_quote( '<?xml version="1.0" encoding="ISO-8859-1"?>' ) . '/', '<?xml version="1.0" encoding="UTF-8"?>', $xmlString);
     }

     $this->_simpleXml = simplexml_load_string( $xmlString );

     if ( !$this->_simpleXml )
     {
       $errorString = 'Failed loading XML from url:' . $url . ' file: '. $request .' method:' . $requestMethod;

       $errorString .= 'parameters:';
       foreach( $parameters as $key => $value)
       {
         $errorString .= ' parameters:' . $key . '=' . $value;
       }

       $errorString .= PHP_EOL . PHP_EOL . 'XML Errors:' . PHP_EOL;
       foreach( libxml_get_errors() as $error ) {
         $errorString .= $error->message . PHP_EOL;
       }

       throw new Exception( $errorString );
     }    

     return $this;
   }


   /**
    * Return the data as a simple XML object
    *
    * @return object Simple XML object
    */
   public function getXml()
   {
     return $this->_simpleXml;
   }

}
?>
