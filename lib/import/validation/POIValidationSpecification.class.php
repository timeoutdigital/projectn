<?php

require_once( dirname( __FILE__ ) . '/BaseValidationSpecification.class.php' );

/**
 * Description of POIValidationSpecification
 *
 * @author clarence
 */
class POIValidationSpecification extends BaseValidationSpecification
{

  /**
   * Validates the poi_name field
   *
   * @param string $value The poi_name field
   *
   * @return boolean
   */
  public function validatePoiName( $value )
  {
    return $this->isNonEmptyString( $value );
  }

  /**
   * Validates the address field
   *
   * @param string $value The address field
   *POIValidationSpecification
   * @return boolean
   */
  public function validateAddress( $value )
  {
    $validates = false;

    $isNonEmptyString = $this->isNonEmptyString( $value );
    $hasWords = preg_match( '/[a-zA-Z]+/i', $value );

    $validates =  $isNonEmptyString && $hasWords;

    return $validates;
  }

  /**
   * Validates the street field
   *
   * @param string $value The street field
   *
   * @return boolean
   */
  public function validateStreet( $value )
  {
    return $this->isNonEmptyString( $value );
  }

  /**
   * Validates the city field
   *
   * @param string $value The city
   *
   * @return boolean
   */
  public function validateCity( $value )
  {
    $validates = false;

    $validates =
      $this->isNonEmptyString( $value )
      && $this->hasWords( $value )
      && $this->isFreeOfOddCharacters( $value )
      ;

    return $validates;
  }

  /**
   * Validates the public_transport_link field
   *
   * @param string $value The public_transport_link
   *
   * @return boolean
   */
  public function validatePublicTransportLink( $value )
  {
    $validates = false;

    $validates =
         $this->isNonEmptyString( $value )
      && $this->hasWords( $value )
      && $this->isFreeOfOddCharacters( $value, ',:' )
      ;

    return $validates;
  }

  /**
   * Validates the longitude and latitude fields
   *
   * @param string $value The longitude or latitude field
   *
   * @return boolean
   */
  public function validateLongituteLatitude( $value )
  {
    $validates = false;

    $validates = is_numeric( $value ) && preg_match( '/\./', $value );

    return $validates;
  }
}
?>
