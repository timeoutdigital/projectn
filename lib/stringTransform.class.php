<?php
/**
 * String Transformer class providing static functions
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @author Tim Bowler <timbowler@timeout.com>
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 *
 */

//Include PEAR Library
require_once 'Validate.php';


class stringTransform
{

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
   * @param string $internationalCode This is the country code e.g. +44
   * @todo finish logging
   * @todo remove the first 0 of function call
   *
   *
   * <b>Example<b>
   * <code>
   * stringTransform::formatPhoneNumber('0207 3577173', '+44');
   * </code>
   *
   */
  public static function formatPhoneNumber($subject, $internationalCode)
  {
    
      if( empty( $subject ) ) return NULL;

      //return if not valid number is is passed in
      if(strlen($subject) < 6)
      {
           //throw new PhoneNumberException('Phone number is incorrect - Less then 6 digits');
          return NULL;
      }

    //Remove any extensions
    $subjectArray = explode(',', $subject);
    $subject = $subjectArray[0];

    $replace = array('2'=>array('a','b','c'),
                     '3'=>array('d','e','f'),
                     '4'=>array('g','h','i'),
                     '5'=>array('j','k','l'),
                     '6'=>array('m','n','o'),
                     '7'=>array('p','q','r','s'),
                     '8'=>array('t','u','v'),
                     '9'=>array('w','x','y','z'));

    // Replace each letter with a number
    // Notice this is case insensitive with the str_ireplace instead of str_replace
    foreach($replace as $digit=>$letters)
    {
      $subject = str_ireplace($letters, $digit, $subject);
    }

    //Remove any non numeric characters
    $subject = trim(preg_replace("/[^0-9]+/", "", $subject));


    //Remove the first 0 from the string
    if(substr($subject, 0, 1) == '0')
    {
         $subject = substr($subject, 1, (strlen($subject)-1));
    }


    $transformedSubject = '';

    switch(strlen($subject))
    {
        case '7':       $transformedSubject = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2", $subject);
        break;
    
        case '8':       $transformedSubject = preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2 $3", $subject);
        break;

        case '10';      $transformedSubject = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2 $3", $subject);
        break;

        case '11':        if (preg_match("/^1800/", $subject))
                          {
                                $transformedSubject = preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2 $3 $4", $subject);
                          }
                          else
                          {
                                $transformedSubject = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2 $3 $4", $subject);
                          }

        break;

        case '12':        $subject = substr($subject, 2, 12);
                          $transformedSubject = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2 $3", $subject);
        break;

        default:          return null;

    }

     return $internationalCode. ' ' .trim($transformedSubject);
   
  }



  /**
   * Return a well formed url
   *
   * @param string $subject The URL
   * @return string The formatted URL
   *
   * <b>Example</b>
   * <code>
   * stringTransform::formatUrl('myurl.com');
   * </code>
   */
  public static function formatUrl($subject)
  {
      //Return if no URL
      if(empty($subject))
      {
        return null;
      }

      //Add http if not already
      if(!preg_match('/^http/', $subject)){

          $subject =  'http://'.$subject;
      }

      try
      {
        $validate = new Validate();
      }
      catch (Exception $e)
      {
        echo "Please install PEAR Validate: sudo pear install Validate-0.8.3";
        exit;
      }

      //Check if domain is valid
      $valid = $validate->uri($subject ,array("allowed_schemes"=>array('https', 'http'),"domain_check"=>true));

      if($valid)
      {
          return $subject;
      }
      else
      {
          return null;
      }

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

  static public function move_CommaThe_FromEndToBeginning( $string )
  {
    $string = trim( $string );
    $string = preg_replace( '/(.*), (The)$/i', '$2 $1', $string );
    return $string;
  }

  /**
   * @param string $allowedTags
   *
   * see http://htmlpurifier.org/docs
   */
  static public function purifyHTML( $html, $allowedTags = 'p,b,a[href],i,br,pre' )
  {
    ProjectConfiguration::registerHTMLPurifier();

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.DefinitionImpl', null);
    $config->set('HTML.Allowed', $allowedTags );
    $htmlPurifier = new HTMLPurifier( $config );
    return $htmlPurifier->purify( $html );
  }
}
?>
