<?php
/**
 * String Transformer class providing static functions
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 * </code>
 *
 */
class stringTransform {

  public static function extractEmailAddressesFromText( $subject )
  {
    $pattern = '/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+/';

    $returnArray = array();

    preg_match_all( $pattern, $subject, $returnArray );
    
    return $returnArray[ 0 ];
  }

  public static function extractUrlsFromText( $subject )
  {
    $pattern = '/((?<=\s)(https?:\/\/)?[\w\d_-]+[.][a-z]{2,}[\w\d:#@%\/;$()~_?\+-=\\\.&]*)/';

    $returnArray = array();

    preg_match_all( $pattern, ' '.$subject, $returnArray );

    return $returnArray[ 0 ];

  }

  public static function extractPhoneNumbersFromText()
  {

  }

  public static function formatPhoneNumber()
  {

  }

  public static function formatUrl()
  {

  }

  /**
   * Takes an array of strings implodes only values that are not blank using $glue
   *
   * <code>
   *   $input = array( 'one', '', 'two', '', three' );
   *   echo Importer::concatNonBlankStrings( ',', $input );
   *
   *   //outputs:
   *   //one, two, three
   * </code>
   *
   * @param array $stringArray
   * @param string $glue
   */
  static public function concatNonBlankStrings( $glue, $stringArray )
  {
    $nonEmptyStrings = array_filter($stringArray, 'stringTransform::concatNonBlankStringsCallBack' );
    return implode($glue, $nonEmptyStrings );
  }

  static private function concatNonBlankStringsCallBack( $string )
  {
    return preg_match( '/\S/', $string );
  }

  static public function stripEmptyLines( $string )
  {
    return preg_replace( '/\r*\n*/', '', $string );
  }

}
?>
