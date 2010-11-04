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

    /**
     * helper function to add images
     *
     * @param Doctrine_Record $storeObject
     * @param SimpleXMLElement | String $url
     */
    protected function addImageHelper( Doctrine_Record $storeObject, $url )
    {
        if ( (string) $url != '' )
        {
            try
            {
                $storeObject->addMediaByUrl( (string) $url );
                return true;
            }
            catch( Exception $e )
            {
                $this->notifyImporterOfFailure( $e );
            }
        }
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

        $this->xml = simplexml_load_string( $curlInstance->getResponse() );
    }
}
class RussiaFeedBaseMapperException extends Exception{}
?>
