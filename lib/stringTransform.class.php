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
 * @see There is some prebuilt logic in the repository (commits of the LisbonFeedListingsMapper and the
 *      LisbonFeedListingsMapperTest class before the 23/11/2010) to parse a string field and produce
 *      occurrences out of it, check for reference if a functionality like this is ever needed
 *
 */

class stringTransform
{

  public function remove_null_values( $array ) {
    foreach( $array as $k => $v )
    {
        if( empty( $array[ $k ] ) ) unset( $array[ $k ] );
        else if( is_array( $array[ $k ] ) )
            $array[ $k ] = stringTransform::remove_null_values( $v );
        if( empty( $array[ $k ] ) ) unset( $array[ $k ] );
    }
    return $array;
  }

  /**
   * Format memory usage to human readable.
   * @return string
   * taken from http://uk2.php.net/manual/en/function.memory-get-usage.php
   */
  public static function byteToHumanReadable( $size )
  {
      if( !is_numeric( $size ) || $size <= 0 )
      {
          return NAN;
      }

    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '. $unit[$i];
  }

   /**
   * Multibyte safe version of trim()
   * Always strips whitespace characters (those equal to \s)
   *
   * @param $string The String to Trim
   * @param $chars Optional list of chars to remove from the String ( as per trim() )
   * @param $chars_array Optional array of preg_quote'd chars to be removed
   * @return string
   */
    public static function mb_trim( $string, $chars = "", $chars_array = array() )
    {
        for( $x=0; $x<iconv_strlen( $chars ); $x++ ) $chars_array[] = preg_quote( iconv_substr( $chars, $x, 1 ) );
        $encoded_char_list = implode( "|", array_merge( array( "\s", "\0", "\x0B" ), $chars_array ) );

        $string = mb_ereg_replace( "^($encoded_char_list)*", "", $string );
        $string = mb_ereg_replace( "($encoded_char_list)*$", "", $string );
        return $string;
    }

  /**
   * Format string into a nice format, result is '09:00' for input of '9'.
   * @return array
   * @todo is this function really needed, or should it be removed?
   */
  public static function formatAsTime( $subject )
  {
    $subject = str_replace( ".", ":", $subject );
    if( strlen( $subject ) == 1 || ( strlen( $subject ) == 4 && substr( $subject, 1, 1 ) == ":" ) )
      $subject = "0" . $subject;
    if( strlen( $subject ) == 2 )
      $subject .= ":00";
    return $subject;
  }

  /**
   * Try to extract time information from a string.
   * eg '10:00', '9.15', '10h' or in the case of '10-12h', return 2 values
   * @return array
   * @todo is this function really needed, or should it be removed?
   */
  public static function extractTimesFromText( $subject )
  {
      $returnArray = array();
      $pattern = '([0-2]?[0-9](((?:\:|\.)[0-5][0-9])|(h|-)))';
      preg_match_all( $pattern, $subject, $returnArray );
      if( !empty( $returnArray[0] ) && array_key_exists( 0, $returnArray ) );
        return $returnArray[0];
      return array();
  }

  /**
   * Try to extract time range information from a string.
   * eg '10-11' or '10.45-12h' or '9h-10h'
   * @return array
   * @todo is this function really needed, or should it be removed?
   */
  public static function extractTimeRangesFromText( $subject )
  {
      $returnArray = array();
      $pattern = '(([0-2]?[0-9]((?:\:|\.)[0-5][0-9])?)h? ?- ?([0-2]?[0-9]((?:\:|\.)[0-5][0-9])?))';
      preg_match_all( $pattern, $subject, $returnArray );
      if( !empty( $returnArray[0] ) && array_key_exists( 0, $returnArray ) );
        return $returnArray[0];
      return array();
  }

  /**
   * Parse about any English textual datetime description into a DB compatible
   * time (H:i:s)
   *
   * @param string $string
   * @return string (a formatted time string)
   */
  public static function toDBTime( $timeString )
  {
      $time = strtotime( $timeString );

      return ( $time === false ) ? null : date( 'H:i:s', $time );
  }

  /**
   * Parse a time string with an fixed format to a DB compatible time string
   *
   * @param string $format
   * @param string $timeString
   * @return string (a formatted time string)
   */
  public static function toDBTimeByFormat( $format, $timeString )
  {
      $time = DateTime::createFromFormat( $format, $timeString );

      return ( $time === false ) ? null : $time->format( 'H:i:s' );
  }

  /**
   * Attempts to grab a start time out of a text blurb
   *
   * @param string $string
   * @param boolean $returnTimeFormatted
   * @return string
   */
  public static function extractStartTime( $string, $returnTimeFormatted = true )
  {
    $matches = array();

    $timeRangePattern = '/^([0-9:]+)-([0-9.:]+)(a|p|A|P)(m|M)$/'; //catches "3:00-7:00pm"

    preg_match( $timeRangePattern, $string, $matches );

    if( count( $matches ) > 0 )
    {
        $string = $matches[1] . strtolower( $matches[3] ) .'m' ; //convert "3:00-7:00pm" to "3:00pm"
    }

    $matches = array();

    $pattern = '/^([0-9]{1,2}([\s]?[\:\.]{1}[0-9]{2})?[\s]?(am|pm|AM|PM)?)[\s]?(\-|to\s)?/';

    preg_match( $pattern, trim( $string ), $matches );

    if ( isset( $matches[ 1 ] ) )
    {
        return ($returnTimeFormatted) ? self::toDBTime( $matches[ 1 ] ) : $matches[ 1 ];
    }
  }

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

      // if the phone is already prefixed with the international dial code
      // we will remove it and it will be added later in this method
      if( strpos( $subject , $internationalCode ) === 0 )
      {
        $subject = substr( $subject , strlen( $internationalCode ) );
      }

      //return if not valid number is is passed in
      if( strlen( $subject ) < 6)
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

        case '9':       $transformedSubject = preg_replace("/^([0-9]{1})([0-9]{4})/", "$1 $2 ", $subject);
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
    //var_dump(trim($transformedSubject));
     return $internationalCode. ' ' .trim($transformedSubject);

  }



  /**
   * Return a well formed url
   *
   * @param unknown_type $subject The URL
   * @return string | NULL The formatted URL
   *
   * <b>Example</b>
   * <code>
   * stringTransform::formatUrl('myurl.com');
   * </code>
   */
  public static function formatUrl( $subject )
  {
      // Basic Validation
      if( !is_string( $subject ) || empty( $subject ) || !is_numeric( strrpos( $subject, '.' ) ) )
      {
        return null;
      }

      // Add HTTP protocol prefix if not defined.
      if( !preg_match( '/^http/', $subject ) )
      {
          $subject =  'http://'.$subject;
      }
      // Validate url
      $validUrl = preg_match( '|^http(s)?://[a-z0-9-]+(\.[a-z]+)*(:[0-9]+)?(/.*)?$|i', $subject );

      // Return Validated Url
      return $validUrl ? $subject : null;
  }

  
  /**
   * Validates email address using a validation strategy discussed at:
   * http://www.linuxjournal.com/article/9585?page=0,3
   *
   * @param unknown_type $subject
   * @return boolean
   */
  public static function isValidEmail( $subject )
  {
        // Basic Validation
        if( !is_string( $subject ) || empty( $subject ) )
        {
            return false;
        }

        // Get location of @ symbol.
        $atIndex = strrpos( $subject, "@" );

        // If no @ symbol, return false
        if( $atIndex === false )
        {
            return false;
        }

        // Cut up email
        $alias        = substr( $subject, 0, $atIndex );
        $domain       = substr( $subject, $atIndex+1 );

        // Validate
        switch( true )
        {
            // Check min and max lengths
            case ( strlen( $alias )  < 1 || strlen( $alias )  > 64 ) :
            case ( strlen( $domain ) < 3 || strlen( $domain ) > 255 ) :

            // Check for alias or domain starting or finishing with a dot '.'
            case ( substr( $alias,  0, 1 ) == '.' || substr( $alias,  -1, 1 ) == '.' ) :
            case ( substr( $domain, 0, 1 ) == '.' || substr( $domain, -1, 1 ) == '.' ) :

            // Domain must contain a dot '.'
            case ( !is_numeric( strrpos( $domain, "." ) ) ) :

            // Regex matching
            case ( preg_match( '/\\.\\./', $alias ) ) :
            case ( preg_match( '/\\.\\./', $domain ) ) :
            case ( !preg_match( '/^[A-Za-z0-9\\-\\.]+$/', $domain ) ) :
            case ( !preg_match( '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace( "\\\\","", $alias ) ) )
                  && ( !preg_match( '/^"(\\\\"|[^"])+"$/', str_replace( "\\\\","", $alias ) ) ) :

            // DNS check
            //case ( !( checkdnsrr( $domain, "MX" ) || checkdnsrr( $domain, "A" ) ) :
                return false;

            default : return true;
        }
  }

  /*
   * @param mixed $minPrice
   * @param mixed $maxPrice
   * @param mixed $currencyPrefix (e.g. currency Symbol)
   * @param string $format optional switch to choose between the formats
   *        supported 'short' and 'long' (default)
   *
   * @return string the price range string
   *
  */
  public static function formatPriceRange( $minPrice, $maxPrice=0, $currencyPrefix='', $format='long' )
  {
    $returnString = '';
    $pricesAreNumeric  = is_numeric( $minPrice ) &&  is_numeric( $maxPrice );
    $pricesAreOverZero = (0 < (int) $minPrice)   &&  (0 <= (int) $maxPrice);

    if ( $pricesAreNumeric && $pricesAreOverZero )
    {
      $moneyFormatString = '%.2n';

      if ( (int) $maxPrice == 0 )
      {
        $returnString = $currencyPrefix . sprintf( '%.2F', $minPrice );
      }
      else
      {
        switch( $format )
        {
          case 'short':
            $returnString = $currencyPrefix . sprintf( '%.2F', $minPrice ) . ' - ' . $currencyPrefix . sprintf( '%.2F', $maxPrice ) ;
            break;
          case 'long':
          default:
            $returnString = 'between ' . $currencyPrefix . sprintf( '%.2F', $minPrice ) . ' and ' . $currencyPrefix . sprintf( '%.2F', $maxPrice ) ;
            break;
        }
      }
    }

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
  static public function purifyHTML( $html, $allowedTags = 'p,b,i,br,pre' )
  {
    ProjectConfiguration::registerHTMLPurifier();

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.DefinitionImpl', null);
    $config->set('HTML.Allowed', $allowedTags );
    $htmlPurifier = new HTMLPurifier( $config );
    return $htmlPurifier->purify( $html );
  }

  /**
   * Removes empty delimiters.
   * Example:
   *
   * <code>
   *   $clean = stringTransform::removeEmptyDelimiters( ',', ', foo, bar, , , baz');
   *   echo $clean;
   *   //outputs 'foo, bar, baz'
   * </code>
   *
   * @param string $delimiter
   * @param string $string
   * @return string
   *
   */
  static public function removeEmptyDelimiters( $delimiter, $string )
  {
    $delimiter = preg_quote( $delimiter );
    $string = preg_replace( "/^($delimiter)*/",            '', $string );
    $string = preg_replace( "/(?<=$delimiter)$delimiter/", '', $string );
    $string = preg_replace( "/$delimiter$/",               '', $string );
    return $string;
  }

  static public function removeTrailingCommas( $string )
  {
    return trim( $string, ', ' );
  }


  /**
   * removes the "meet at" from the street names
   * returns an array with "street" and "additional_address_details" keys
   * if the street name has " at " the string after at is added to  "additional_address_details"
   *
   * @param string $street
   * @return array()
   */
  static public function parseStreetName( $street )
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

  static public function reverseHTMLChars( $string )
  {
      // Known List of HTML Chars > codes
      // @todo: Some of these are duplicated, should remove or move to another array
      // @Source: http://uk2.php.net/manual/en/function.get-html-translation-table.php#73410
      $charTable = array( '&apos;'=>'&#39;', '&minus;'=>'&#45;', '&circ;'=>'&#94;', '&tilde;'=>'&#126;', '&Scaron;'=>'&#138;',
                            '&lsaquo;'=>'&#139;', '&OElig;'=>'&#140;', '&lsquo;'=>'&#145;', '&rsquo;'=>'&#146;', '&ldquo;'=>'&#147;',
                            '&rdquo;'=>'&#148;', '&bull;'=>'&#149;', '&ndash;'=>'&#150;', '&mdash;'=>'&#151;', '&tilde;'=>'&#152;',
                            '&trade;'=>'&#153;', '&scaron;'=>'&#154;', '&rsaquo;'=>'&#155;', '&oelig;'=>'&#156;', '&Yuml;'=>'&#159;',
                            '&yuml;'=>'&#255;', '&OElig;'=>'&#338;', '&oelig;'=>'&#339;', '&Scaron;'=>'&#352;', '&scaron;'=>'&#353;',
                            '&Yuml;'=>'&#376;', '&fnof;'=>'&#402;', '&circ;'=>'&#710;', '&tilde;'=>'&#732;', '&Alpha;'=>'&#913;',
                            '&Beta;'=>'&#914;', '&Gamma;'=>'&#915;', '&Delta;'=>'&#916;', '&Epsilon;'=>'&#917;', '&Zeta;'=>'&#918;',
                            '&Eta;'=>'&#919;', '&Theta;'=>'&#920;', '&Iota;'=>'&#921;', '&Kappa;'=>'&#922;', '&Lambda;'=>'&#923;',
                            '&Mu;'=>'&#924;', '&Nu;'=>'&#925;', '&Xi;'=>'&#926;', '&Omicron;'=>'&#927;', '&Pi;'=>'&#928;', '&Rho;'=>'&#929;',
                            '&Sigma;'=>'&#931;', '&Tau;'=>'&#932;', '&Upsilon;'=>'&#933;', '&Phi;'=>'&#934;', '&Chi;'=>'&#935;', '&Psi;'=>'&#936;',
                            '&Omega;'=>'&#937;', '&alpha;'=>'&#945;', '&beta;'=>'&#946;', '&gamma;'=>'&#947;', '&delta;'=>'&#948;', '&epsilon;'=>'&#949;',
                            '&zeta;'=>'&#950;', '&eta;'=>'&#951;', '&theta;'=>'&#952;', '&iota;'=>'&#953;', '&kappa;'=>'&#954;', '&lambda;'=>'&#955;',
                            '&mu;'=>'&#956;', '&nu;'=>'&#957;', '&xi;'=>'&#958;', '&omicron;'=>'&#959;', '&pi;'=>'&#960;', '&rho;'=>'&#961;',
                            '&sigmaf;'=>'&#962;', '&sigma;'=>'&#963;', '&tau;'=>'&#964;', '&upsilon;'=>'&#965;', '&phi;'=>'&#966;', '&chi;'=>'&#967;',
                            '&psi;'=>'&#968;', '&omega;'=>'&#969;', '&thetasym;'=>'&#977;', '&upsih;'=>'&#978;', '&piv;'=>'&#982;', '&ensp;'=>'&#8194;',
                            '&emsp;'=>'&#8195;', '&thinsp;'=>'&#8201;', '&zwnj;'=>'&#8204;', '&zwj;'=>'&#8205;', '&lrm;'=>'&#8206;', '&rlm;'=>'&#8207;',
                            '&ndash;'=>'&#8211;', '&mdash;'=>'&#8212;',  '&sbquo;'=>'&#8218;', '&ldquo;'=>'&#8220;', '&rdquo;'=>'&#8221;', '&bdquo;'=>'&#8222;',
                            '&dagger;'=>'&#8224;', '&Dagger;'=>'&#8225;', '&bull;'=>'&#8226;', '&hellip;'=>'&#8230;', '&permil;'=>'&#8240;', '&prime;'=>'&#8242;',
                            '&Prime;'=>'&#8243;', '&lsaquo;'=>'&#8249;', '&rsaquo;'=>'&#8250;', '&oline;'=>'&#8254;', '&frasl;'=>'&#8260;', '&image;'=>'&#8465;',
                            '&weierp;'=>'&#8472;', '&real;'=>'&#8476;', '&trade;'=>'&#8482;', '&alefsym;'=>'&#8501;', '&larr;'=>'&#8592;', '&uarr;'=>'&#8593;',
                            '&rarr;'=>'&#8594;', '&darr;'=>'&#8595;', '&harr;'=>'&#8596;', '&crarr;'=>'&#8629;', '&lArr;'=>'&#8656;', '&uArr;'=>'&#8657;',
                            '&rArr;'=>'&#8658;', '&dArr;'=>'&#8659;', '&hArr;'=>'&#8660;', '&forall;'=>'&#8704;', '&part;'=>'&#8706;', '&exist;'=>'&#8707;',
                            '&empty;'=>'&#8709;', '&nabla;'=>'&#8711;', '&isin;'=>'&#8712;', '&notin;'=>'&#8713;', '&ni;'=>'&#8715;', '&prod;'=>'&#8719;',
                            '&sum;'=>'&#8721;', '&minus;'=>'&#8722;', '&lowast;'=>'&#8727;', '&radic;'=>'&#8730;', '&prop;'=>'&#8733;', '&infin;'=>'&#8734;',
                            '&ang;'=>'&#8736;', '&and;'=>'&#8743;', '&or;'=>'&#8744;', '&cap;'=>'&#8745;', '&cup;'=>'&#8746;', '&int;'=>'&#8747;', '&there4;'=>'&#8756;',
                            '&sim;'=>'&#8764;', '&cong;'=>'&#8773;', '&asymp;'=>'&#8776;', '&ne;'=>'&#8800;', '&equiv;'=>'&#8801;', '&le;'=>'&#8804;', '&ge;'=>'&#8805;',
                            '&sub;'=>'&#8834;', '&sup;'=>'&#8835;', '&nsub;'=>'&#8836;', '&sube;'=>'&#8838;', '&supe;'=>'&#8839;', '&oplus;'=>'&#8853;',
                            '&otimes;'=>'&#8855;', '&perp;'=>'&#8869;', '&sdot;'=>'&#8901;', '&lceil;'=>'&#8968;', '&rceil;'=>'&#8969;', '&lfloor;'=>'&#8970;',
                            '&rfloor;'=>'&#8971;', '&lang;'=>'&#9001;', '&rang;'=>'&#9002;', '&loz;'=>'&#9674;', '&spades;'=>'&#9824;', '&clubs;'=>'&#9827;',
                            '&hearts;'=>'&#9829;', '&diams;'=>'&#9830;' ); // @Duplicates:'&lsquo;'=>'&#8216;', '&rsquo;'=>'&#8217;'

      // Extract the Keys & Values
      $charKeys   = array_keys( $charTable );
      $charValues = array_values( $charTable );

      // Replace Values with Keys, Returned value require html_entity _decode()
      return str_replace( $charValues, $charKeys, $string);

  }

  /**
   * Replace multiple libe Breaks( 2 or more ) into Sinle line break
   * @param string $string
   * @return string
   */
  static public function removeMultipleLines( $string )
  {
      return mb_ereg_replace( "\n+", "\n", $string );
  }

  /**
   * Validate Exported Record ID
   * @param string $recordID
   * @return boolean
   */
  static public function isValidExportRecordID( $recordID )
  {
      if( preg_match( '#^[A-Z]{3}[0-9]{30}$#', $recordID ) == 0 )
      {
          return false;
      }

      return true;
  }
}
