<?php
/**
 * Class for importing Ny Eating and Drinking feeds.
 *
 * @package ny.import.lib.projectn
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * 
 * @copyright Timeout Communications Ltd.
 * @version 1.0.0
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
    $poi = new Poi();
    $poi->setPoiName( $poiData[ 'name' ] );
    $poi->setStreet( $poiData[ 'address' ] );
    $poi->setCity( $poiData[ 'city' ] );
    $poi->setCountry( 'United States of America' );
    $poi->setCountryCode( 'us' );
    $poi->setLocalLanguage( 'en' );
    $poi->setAdditionalAddressDetails( $poiData[ 'directions' ] );
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
    $category =  Doctrine::getTable('PoiCategory')->findOneByName( 'restaurant' );

    $poi->setPoiCategoryId( $category->getId() );

    //save to database
    $poi->save();

    //Kill the object
    $poi->free();

  }


  
}