<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of POIValidationSpecification
 *
 * @author clarence
 */
class POIValidationSpecification
{

  /**
   * Validates the poi_name field
   *
   * @param string $value The poi_name field
   *
   * @return boolean
   */
  static public function validatePoiName( $value )
  {
    return POIValidationSpecification::isNonEmptyString( $value );
  }

  /**
   * Checks that a value is a string and not empty
   *
   * @param string $value A value to be tested
   *
   * @return boolean
   */
  static public function isNonEmptyString( $value )
  {
    $isNonEmptyString = false;

    $isString = is_string( $value );
    $regexp   = (boolean) strlen( $value );

    $isNonEmptyString = $regexp && $isString;

    return $isNonEmptyString;
  }

  /**
   * Checks that a value has words, not just spaces and/or numbers
   *
   * @param string $value A value to be tested
   *
   * @return boolean
   */
  static public function hasWords( $value )
  {
    return (boolean) preg_match( '/[a-zA-Z]+/i', $value );
  }

  /**
   * Checks that a value has only the following characters:
   * - alphanumeric chars
   * - white space
   * - hyphen
   *
   * @paraprivatem string $value A value to be tested
   *
   * @return boolean
   */
  static public function isFreeOfOddCharacters( $value, $except='' )
  {
    return (boolean) !preg_match( "/[^-a-zA-Z0-9 $except]/i", $value );
  }

  /**
   * Validates the address field
   *
   * @param string $value The address field
   *POIValidationSpecification
   * @return boolean
   */
  static public function validateAddress( $value )
  {
    $validates = false;

    $isNonEmptyString = POIValidationSpecification::isNonEmptyString( $value );
    $hasWords = preg_match( '/[a-zA-Z]+/i', $value );

    $validates =  $isNonEmptyString && $hasWords;

    return $validates;
  }
}
?>
