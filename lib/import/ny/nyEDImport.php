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
    $poi = new Poi();
    $poi->setPoiName( $poiData[ 'name' ] );

    $streetAddress = (string) $poiData[ 'address' ];
    $pos = strpos( $streetAddress, "between" );
    if( $pos !== false )
    {
        $betweenSection = substr( $streetAddress, $pos );
        $streetAddress = substr( $streetAddress, 0, $pos );
        $additionalAddressDetails = isset( $betweenSection ) ? $betweenSection . ", " . $poiData[ 'directions' ] : $poiData[ 'directions' ];
    }else
    {
       $streetInfo = $this->parseStreetName( $streetAddress );
       $streetAddress = $streetInfo[ 'street' ];
       $additionalAddressDetails = $streetInfo[ 'additional_address_details' ];
    }

    $poi->setStreet( $streetAddress );
    $poi->setAdditionalAddressDetails( $additionalAddressDetails );
    $poi->setCity( $poiData[ 'city' ] );
    $poi->setCountry( 'United States of America' );
    $poi->setCountryCode( 'us' );
    $poi->setLocalLanguage( 'en' );


    $poi->setUrl( $poiData[ 'website' ] );
    $poi->setVendorId( $this->_vendor->getId() );
    $poi->setPhone( $poiData[ 'phone' ] );


    $poi->setPoiName( $poiData[ 'group_review' ] );

    $poiObj[ 'geocode_look_up' ] = stringTransform::concatNonBlankStrings( ', ', array( $poiData[ 'address' ], $poiData[ 'city' ], $poiData[ 'country' ]   ) );

    //Get and set the child category
    $categoriesArray = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' ) );
    $categoriesArray[] = Doctrine::getTable('PoiCategory')->findOneByName( 'restaurant' );
    $poi['PoiCategory'] =  $categoriesArray;

    //save to database
    $poi->save();

    //Kill the object
    $poi->free();

  }

  /**
   * removes the "meet at" from the street names
   * returns an array with "street" and "additional_address_details" keys
   * if the street name has " at " the string after at is added to  "additional_address_details"
   *
   * @param string $street
   * @return array()
   */
  private function parseStreetName( $street )
  {
    //first remove 'meet at's
    $street = str_replace( 'meet at', '', $street );

    $parts = explode( ' at ', $street );

    if( count( $parts ) == 2 )
    {
        return array( 'street' => ucfirst ( trim( $parts[0] ) ) ,
                    'additional_address_details' => 'At ' .trim( $parts[1] ) );
    }
    else
    {
        return array( 'street' => ucfirst ( trim( $street ) ) ,
                  'additional_address_details' => NULL );
    }

  }




}
