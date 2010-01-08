<?php

/**
 * Description of ImportValidationSpecificationclass
 *
 * @author clarence
 */
class BaseValidationSpecification
{
    
  /**
   * Checks that a value is a string and not empty
   *
   * @param string $value A value to be tested
   *
   * @return boolean
   */
  public function isNonEmptyString( $value )
  {
    $isNonEmptyString = false;

    $isString = is_string( $value );
    $regexp   = preg_match( '/\S/', $value );

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
  public function hasWords( $value )
  {
    return (boolean) preg_match( '/[a-z]+/i', $value );
  }

  /**
   * Checks that a value has only the following characters:
   * - alphanumeric chars
   * - white space
   * - hyphen
   *
   * @param string $value A value to be tested
   *
   * @return boolean
   */
  public function isFreeOfOddCharacters( $value, $except='' )
  {
    return (boolean) !preg_match( "/[^-a-zA-Z0-9 $except]/i", $value );
  }
}
?>
