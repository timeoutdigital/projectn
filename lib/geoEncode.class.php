<?php
/**
 * Gets geo data using Google.
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Tim Bowler <timbowler@timeout.com>
 * @copyright Timeout Communication Ltd &copyright; 2009
 *
 * @version 1.0.0
 *
 * <b>Example of usage:</b>
 *
 * The addressString must contain as much address info as possible
 *  <code>
 *
 *  $geoEncode = new geoEncode();
 *  $geoEncode->setAddress( $addressString );
 *
 *  //Set longitude and latitude
 *  $poiObj[ 'longitude' ] = $geoEncode->getLongitude();
 *  $poiObj[ 'latitude' ]  = $geoEncode->getLatitude();
 * </code>
 *
 * If a geocode cannot be found then an Exception is thrown
 *
 *
 * Google Documentation
 * @link http://code.google.com/apis/maps/documentation/services.html#Geocoding
 * @link http://code.google.com/apis/maps/documentation/reference.html#GGeoStatusCode.G_GEO_SUCCESS
 * 
 */
class geoEncode
{

  private  $addressString;
  private  $longitude;
  private  $latitude;
  private  $accuracy;
  private  $vendorObj;
  private  $lookupUrl;
  private  $response;


  /**
   * 
   */
  public function  __construct()
  {
  }


  /**
   * Set the address
   *
   * @param string $address String
   *
   * @return void
   * 
   */
  public function setAddress( $address, Vendor $vendorObj=null )
  {
    $this->addressString = urlencode($address);

    $this->vendorObj = $vendorObj;

    $this->getGeoCode();
  }


  /**
   *
   * Get the Longitude and Latitude details
   *
   * @param string How to return the data. array | string
   *
   */
  public function getGeoCode( $apiKey = NULL )
  {
     if( !is_string( $apiKey ) || strlen( $apiKey ) != 86 ) $apiKey = sfConfig::get('app_google_api_key');
     
     $geoCode = "http://maps.google.com/maps/geo?q=".$this->addressString."&output=csv&oe=utf8\&sensor=false&key=". $apiKey;

     if( !is_null( $this->vendorObj ) )
     {
         $geoCode .= '&region='.$this->vendorObj['country_code'];
         $geoCode .= '&bounds='.$this->vendorObj->getGoogleApiGeoBounds();
     }
     
     //Set the string at a class level
     $this->lookupUrl = $geoCode;


     //echo "\n".$geoCode . "\n";

     //Setup curl
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $geoCode);
     curl_setopt($ch, CURLOPT_HEADER,0); //Change this to a 1 to return headers
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

     $this->response = curl_exec($ch);
     curl_close($ch);

     //Create an array containing the data
     $dataArray = explode(',', $this->response);


     /**
      * @todo re-implement exception handling
      */
     switch($dataArray[0])
     {
         case '602': throw new GeoCodeException('G_GEO_UNKNOWN_ADDRESS');
             break;

         case '603': //throw new GeoCodeException('G_GEO_UNAVAILABLE_ADDRESS');
             break;

         case '620': //throw new GeoCodeException('G_GEO_TOO_MANY_QUERIES');
             break;

     }

     if($dataArray[0] != '200')
     {
          unset($dataArray[2]);
          unset($dataArray[3]);
     }

     $this->setCoOrdinates($dataArray);

     //print_r($dataArray);
     //Set invidual co-ords
   
     return $this;
  }

  public function getRawResponse()
  {
      return $this->response;
  }


  /**
   *  Break apart data and set class variables.
   *
   *  @param array $dataArray The geo co-ords
   */
  public function setCoOrdinates($dataArray)
  {

    $this->longitude =  ( isset( $dataArray[3] ) ? (float) $dataArray[3]: null );
    $this->latitude  =  ( isset( $dataArray[2] ) ? (float) $dataArray[2]: null );
    $this->accuracy  =  ( isset( $dataArray[1] ) ? (int) $dataArray[1]: 0 );

  }

  /**
   * Get the longitude fo rthe address
   */
  public function getLongitude()
  {
    return $this->longitude;
  }


  /**
   * Get the latitude of the address
   */
  public function getLatitude()
  {
    return $this->latitude;
  }


  /**
   * Get the accuracy of the address
   */
  public function getAccuracy()
  {
    return $this->accuracy;
  }

  public function getLookupUrl()
  {
    return $this->lookupUrl;
  }


}

?>
