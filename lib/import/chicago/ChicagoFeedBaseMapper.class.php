<?php
/**
 * Chicago Feed Base mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class ChicagoFeedBaseMapper extends DataMapper
{
    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    protected $vendorID;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

   /**
    *
    * @param Doctrine_Record $vendor
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    */
    public function __construct( Doctrine_Record $vendor, SimpleXMLElement $xml, geoEncode $geoEncoder = null)
    {

        if( !$vendor )
        {
            // Find vendor
            $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'chicago', 'en-US' );
        }
        
        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'ChicagoFeedBaseMapper:: Vendor not found' );

        // Set data
        $this->geoEncoder           = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->vendor               = $vendor;
        $this->xml                  = $xml;
        $this->vendorID             = $vendor['id'];
    }

    /**
     * Get Nodes of given Xpath in $XML
     * @param string $nodePath
     * @param SimpleXMLElement $xml
     * @return Array
     */
    protected function getXMLNodesByPath( $nodePath, SimpleXMLElement $xml = null )
    {
        if( !$xml )
            $xml = $this->xml;

        return $xml->xpath( $nodePath );
    }
}
?>
