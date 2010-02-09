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

  /**
   *
   * 
   * <b>Standards for internatioanl numbers: http://en.wikipedia.org/wiki/E.123</b>
   *
   * E.g. +31 42 123 4567
   *
   * @param string $subject The telephone number to be tested/transformed
   *
   */
  public static function formatPhoneNumber($subject)
  {
    $subject = preg_replace("/[^0-9]/", "", $subject);

    return $subject;
  }

  public static function formatUrl()
  {

  }

  /*
   * formatPriceRange formats a from to price range, make sure the locale
   * is set correctly
   *
   * @param mixed $minPrice
   * @param mixed $maxPrice
   * @param string $format optional switch to choose between the formats
   *        supported 'short' and 'long' (default)
   *
   * @return string the price range string
   *
   */
  public static function formatPriceRange( $minPrice, $maxPrice, $format='long' )
  {
    $returnString = '';

    if ( is_numeric( $minPrice ) && is_numeric( $maxPrice ) && 0 < (int) $minPrice &&  0 <= (int) $maxPrice )
    {
      $moneyFormatString = '%.2n';

      if ( (int) $maxPrice == 0 )
      {
        $returnString = money_format( $moneyFormatString, $minPrice );
      }
      else
      {
        switch( $format )
        {
          case 'short':
            $returnString = money_format( $moneyFormatString, $minPrice ) . ' - ' . money_format( $moneyFormatString, $maxPrice ) ;
            break;
          case 'long':
          default:
            $returnString = 'between ' . money_format( $moneyFormatString, $minPrice ) . ' and ' . money_format( $moneyFormatString, $maxPrice ) ;
            break;
        }
      }
    }

    //fix the issue with the missing space in front of £
    $returnString = str_replace( '£', '£ ', $returnString );

    return $returnString;
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
