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
  private  $apiKey = 'ABQIAAAA_FL2s5FUf4LMO_mUYDXUABSveoBGowI7nqh6kHEuOnD_thDOzhQRISkLudF55CGQjHRYPqfn429wzw';
  private  $longitude;
  private  $latitude;
  private  $accuracy;


  /**
   * Instanciate object with 
   *
   * @param string Address
   * @param $apiKey
   */
  public function  __construct(){ }


  /**
   * Set the address
   *
   * @param string $address String
   *
   * @return void
   * 
   */
  public function setAddress($address)
  {
    $this->addressString = urlencode($address);
    $this->getGeoCode();
  }


  /**
   *
   * Get the Longitude and Latitude details
   *
   * @param string How to return the data. array | string
   *
   */
  public function getGeoCode()
  {
     $geoCode = "http://maps.google.com/maps/geo?q=".$this->addressString."&output=csv&oe=utf8\&sensor=false&key=". $this->apiKey;


     //Setup curl
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $geoCode);
     curl_setopt($ch, CURLOPT_HEADER,0); //Change this to a 1 to return headers
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

     $data = curl_exec($ch);
     curl_close($ch);

     //Create an array containing the data
     $dataArray = explode(',', $data);


     /**
      * @todo re-implement exception handling
      */
     switch($dataArray[0])
     {
         case '602': //throw new GeoCodeException('G_GEO_UNKNOWN_ADDRESS');
             break;

         case '603': //throw new GeoCodeException('G_GEO_UNAVAILABLE_ADDRESS');
             break;

         case '620': //throw new GeoCodeException('G_GEO_TOO_MANY_QUERIES');
             break;

     }

     if($dataArray[0] != '200')
     {
         //throw new Exception('No Geocode available: Code = '.$dataArray[0]);
     }

     //Set invidual co-ords
     $this->setCoOrdinates($dataArray);

     return $this;
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

}

?>
