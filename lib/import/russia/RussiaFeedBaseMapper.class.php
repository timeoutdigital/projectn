<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class RussiaFeedBaseMapper extends DataMapper
{
    /**
    *
    * @var projectNDataMapperHelper
    */
    protected $dataMapperHelper;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    protected $params;

    protected $exceptionClass = 'RussiaFeedBaseMapperException';
    /**
    *
    * @param SimpleXMLElement $xml
    * @param geocoder $geocoderr
    * @param string $city
    */
    public function __construct( Vendor $vendor, $params )
    {
        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'Vendor not found.' );

        $this->_validateConstructorParams( $vendor, $params );
        $this->_loadXML( $vendor, $params );
        
        $this->vendor               = $vendor;
        $this->params               = $params; // required for SPLIT option in places
        
    }

    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );

        return $string;
    }

    protected function roundNumberOrReturnNull( $string )
    {
        return is_numeric( (string) $string ) ? round( (string) $string ) : null;
    }

    protected function extractTimeOrNull( $string )
    {
        $date = DateTime::createFromFormat( 'H:i', $string );

        return ( $date === false ) ? null : $string;
    }

    protected function clean( $string , $chars = '' )
    {
        return stringTransform::mb_trim( $string, $chars );
    }

    protected function removeQuotAmp( $string )
    {
        
        return mb_eregi_replace('&quot;|&amp;', '', $string);
    }

    private function _validateConstructorParams( $vendor, $params )
    {
        if( !( $vendor instanceof Vendor ) || !isset( $vendor[ 'id' ] ) )
        {
            throw new $this->exceptionClass( 'Invalid Vendor Passed to RussiaFeedBaseMapper Constructor.' );
        }

        if( !isset( $params['curl']['classname'] ) || !isset( $params['curl']['src'] ) || !isset( $params['type'] ) )
        {
            throw new $this->exceptionClass( 'Invalid Params Passed to RussiaFeedBaseMapper Constructor.' );
        }
    }

    private function _loadXML( $vendor, $params )
    {
        $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
        $curlInstance->exec();

        new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );

        $fixer = new xmlDataFixer( $curlInstance->getResponse() );
        $fixer->removeVerticalTab();
        $this->xml = $fixer->getSimpleXML();
    }

    /**
     * Format russian telephone numbers and return VALID number or Null #837
     * @param string $phoneNumber
     * @return mixed
     */
    protected function getFormattedAndFixedPhone( $phoneNumber )
    {
        if( $phoneNumber == null )
        {
            return null;
        }

        // Remov Extensions and Brack contents
        $phoneFixer = new phoneNumberFixer( $phoneNumber);
        $phoneFixer->removeBracketContents();
        $phoneFixer->removeExtensionNumber();
        $phoneNumber = $phoneFixer->getPhoneNumber();

        // Normal Telephone number Length = 7
        // Area/City/Mobile code length = 3 (821, 911, 921, 951 etc...)
        // hence we should have have maximum of 7 numbers without area code or 10 digits with area code
        // sometime russian cities have 8 front of numbers or +7 front of it,
        // bcz you will need 8 to dial from one city to another inside russia

        // Remove everything otherthan Number
        $phoneNumber = trim( preg_replace( "/[^0-9]+/", "", $phoneNumber ) );

        if( strlen( $phoneNumber ) == 7 )
        {
            // add Area code to the Number and Return
            return $this->params['phone']['areacode'] . $phoneNumber;
        } else if ( strlen( $phoneNumber ) >= 10 )
        {
            // Get the LAST 10 Digits ( 7 Phone numbers and 3 City code = 10 ) and return
            return substr( $phoneNumber, -10 );
        } else {
            $this->notifyImporterOfFailure( new RussiaFeedBaseMapperException( 'Invalid Telephone Number: ' .  $phoneNumber . ' For city : ' . $this->vendor['city'] ) );
        }

        return null; // Don't return phone number when unable to format the numbers
    }
}
class RussiaFeedBaseMapperException extends Exception{}
?>
