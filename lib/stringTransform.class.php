<?php
/**
 * String Transformer class providing static functions
 *
 * @package projectn
 * @subpackage lib
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

}
?>
