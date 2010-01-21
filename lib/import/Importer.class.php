<?php
/**
 * @package import.lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 */
abstract class Importer
{

  /**
   * Takes an array of strings implodes only values that are not blank using $glue
   * 
   * @param array $stringArray
   * @param string $glue
   */
  static public function concatNonBlankStrings( $glue, $stringArray )
  {
    $nonEmptyStrings = array_filter($stringArray, 'Importer::concatNonBlankStringsCallBack' );
    return implode($glue, $nonEmptyStrings );
  }

  static private function concatNonBlankStringsCallBack( $string )
  {
    return preg_match( '/\S/', $string );
  }

  abstract public function run();
}
