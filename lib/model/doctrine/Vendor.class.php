<?php

/**
 * Vendor
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Vendor extends BaseVendor
{

  /**
   * Returns the concatenated (city_language) vendor name
   * 
   * @return string vendor_name 
   */
  public function getName()
  {
    return $this->city . '_' . $this->getLanguage();
  }

  /**
   * Returns the geo boundaries for the vendor area in a format that google uses.
   *
   * @return string google_bounds
   */
  public function getGoogleApiGeoBounds()
  {
    $bounds_array = explode( ";", $this['geo_boundries'] );
    return $bounds_array[0] . "," . $bounds_array[1] . "|" . $bounds_array[2] . "," . $bounds_array[3];
  }

  /**
   * returns the utc offset in the following format +00:00
   * for a given date or for 'now' by default if no date
   * passed
   *
   * @param string $dateTimeString
   * @return string
   */
  public function getUtcOffset( $dateTimeString = 'now' )
  {
    $zoneObj = new DateTimeZone( $this[ 'time_zone' ] );
    $dateTimeObj = new DateTime( $dateTimeString, $zoneObj ) ;

    return $dateTimeObj->format( 'P' );
  }

  /**
   * Returns an array of address transformations appropriate to this lender
   *
   * @param array An array of all transformations, indexed by vendor ID. By default null, loads from app.yml
   * @return array An array of transformations for this lender
   */
  public function getAddressTransformations( $transformations = null )
  {

    if ( $transformations == null || !is_array( $transformations ) )
      $transformations = sfConfig::get( 'app_vendor_address_transformations', array() );

    return ( isset( $transformations[ $this[ 'id' ] ] ) ) ? $transformations[ $this[ 'id' ] ] : array();

  }

  /**
   * Check Latitude and Logitude against vendoe geocode boundaries and return TRUE if within and FALSE when outside boundaries
   * @param float $latitude
   * @param float $longitude
   * @return boolean
   */
  public function isGeocodeWithinVendorBoundaries( $latitude, $longitude )
  {
      if( !is_numeric( $latitude ) || !is_numeric( $longitude ) )
      {
          return false;
      }

      $bounds_array = explode( ";", $this['geo_boundries'] );
      if( $latitude < $bounds_array[0] || $latitude > $bounds_array[2] ||
          $longitude < $bounds_array[1] || $longitude > $bounds_array[3] )
      {
          return false;
      }

      return true;
  }

}
