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
class HongKongFeedBaseMapper extends DataMapper
{
    /**
     *
     * @var array
     */
    protected $params;
    
    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    * @param string $city
    */
    public function __construct( Vendor $vendor, $params)
    {
        if( !isset( $vendor ) || !$vendor )
        {
            throw new Exception( 'Vendor not found.' );
        }

        // validate Params / Datasource
        if( !is_array( $params ) || count( $params ) <= 0 )
        {
            throw new Exception( ' invalid Parameter, should be an array' );
        }

        // validate data source / url
        if( !isset( $params['curl'] ) || !is_array($params['curl']) || empty( $params['curl'] ) )
        {
            throw new Exception( 'No Datasource specified' );
        }
        if( !isset( $params['curl']['src'] ) || trim( $params['curl']['src'] ) == '' )
        {
            throw new Exception( 'Invaid Datasource URL specified' );
        }
        if( !isset( $params['curl']['classname'] ) || trim( $params['curl']['classname'] ) == '' )
        {
            throw new Exception( 'Invalid Datasource Classname specified' );
        }

        $this->vendor               = $vendor;
        $this->params               = $params;

        // Download the Feed
        $this->getXMlFeed();
    }

    protected function fixHtmlEntities( $string )
    {
        // Remove Multiple lines
        $string = stringTransform::removeMultipleLines( $string );

        // HTML Decode
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );
        
        // Remove everything inside table and table tag
        $string = mb_ereg_replace( '</?table[^>]*>(.+</table[^>]*>|)', '', $string );
        
        // Remove Multiple BR
        /*$string = mb_ereg_replace("(<br\s*\/?>\s*)+", "<br/>", $string);*/

        // Remove Empty Tags
        // $string = preg_replace('|<(\w+)[^>]*></\1>|','', $string);
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

    /**
     * Download XML Feed from url in params Variable
     */
    protected function getXMlFeed()
    {
        // Get the Feed
        $curl = new $this->params['curl']['classname']( $this->params['curl']['src'] );
        $curl->exec();

        // Archive
        new FeedArchiver( $this->vendor, $curl->getResponse(), $this->params['type'] );
        
        // Go through Cleaning process and Parse it as XML
        $xmlDataFixer = new xmlDataFixer( $curl->getResponse() );

        // Call override cleaner
        $this->getXMLFeedCleanUp( $xmlDataFixer );
        
        $xmlDataFixer->removeHtmlEntiryEncoding();
        $xmlDataFixer->encodeUTF8();
        $this->xml = $xmlDataFixer->getSimpleXML(); // get the XMl file and Set it in Class Variable
    }

    /**
     * Override this function to Filter Mapper specific fields
     * @param xmlDataFixer $xmlDataFixer
     */
    protected function getXMLFeedCleanUp( xmlDataFixer $xmlDataFixer )
    {
        $xmlDataFixer->removeMSWordHtmlTags();
    }
}
?>
