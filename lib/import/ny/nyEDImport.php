<?php
/**
 * Class for importing Ny Eating and Drinking feeds.
 *
 * @package projectn
 * @subpackage ny.import.lib
 * 
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * 
 * @copyright Timeout Communications Ltd.  &copyright; 2009
 * @version 1.0.0
 *
 * @todo finish class - currently awaiting verification as to whether its now needed.
 *
 *
 */


class importNyED
{
  
  private $_csv;
  private $_vendor;

  
  /**
   * Constructor
   *
   * @param processCsv object $csv
   * @param Vendor object $vendor
   *
   */
  public function  __construct( $csv, $vendor )
  {
    throw new Exception( 'This class is suspicious. Dont use it, it doesnt update pois, just adds new ones.' );
    $this->_csv = $csv;
    $this->_vendor = $vendor;
  }



  /**
   * Insert the venues
   *
   *
   */
  public function insertPois()
  {

    $data = $this->_csv->getCsvAsArray();

    foreach( $data as $poi )
    {
      $this->insertPoi( $poi );
    }

    return true;
  }

  public function insertPoi( $poiData )
  {
    //Set the Poi's required values
    var_dump( $poiData );
    $poi = new Poi();
    $poi->setPoiName( $poiData[ 'name' ] );

    $streetAddress = (string) $poiData[ 'address' ];
    $pos = strpos( $streetAddress, "between" );
    if( $pos !== false )
    {
        $betweenSection = substr( $streetAddress, $pos );
        $streetAddress = substr( $streetAddress, 0, $pos );
    }

    $poi->setStreet( $streetAddress );
    $poi->setCity( $poiData[ 'city' ] );
    $poi->setCountry( 'United States of America' );
    $poi->setCountryCode( 'us' );
    $poi->setLocalLanguage( 'en' );

    $additionalAddressDetails = isset( $betweenSection ) ? $betweenSection . ", " . $poiData[ 'directions' ] : $poiData[ 'directions' ];
    $poi->setAdditionalAddressDetails( $additionalAddressDetails );
    
    $poi->setUrl( $poiData[ 'website' ] );
    $poi->setVendorId( $this->_vendor->getId() );
    $poi->setPhone( $poiData[ 'phone' ] );


    $poi->setPoiName( $poiData[ 'group_review' ] );


    //Full address String
    $name = $venue->identifier;
    $street = $poiData[ 'address' ];
    $town = $poiData[ 'city' ];
    /*$country = $venue->country_symbol;
    $state = $venue->state;
    $suburb = $venue->suburb;
    $district = $venue->district;
    $addressString = "$name, $street, $district, $suburb, $town, $country, $state";*/

    $addressString = "$name, $street, $town";

    //Get longitude and latitude for venue
    $geoEncode = new geoEncode();
    $geoEncode->setAddress( $addressString );

    //Set longitude and latitude
    $poi->setLongitude( $geoEncode->getLongitude()) ;
    $poi->setLatitude( $geoEncode->getLatitude() );

    //Get and set the child category
    $categoriesArray = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' ) );
    $categoriesArray[] = Doctrine::getTable('PoiCategory')->findOneByName( 'restaurant' );
    $poi['PoiCategory'] =  $categoriesArray;

    //save to database
    $poi->save();

    //Kill the object
    $poi->free();

  }


  
}
