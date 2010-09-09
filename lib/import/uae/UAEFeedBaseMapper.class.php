<?php
/**
 * UAE Feed Base Mapper
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
class UAEFeedBaseMapper extends DataMapper
{
    private $vendor;
    private $xml;
    private $geocoder;

    public function  __construct( SimpleXMLElement $xml, Doctrine_Record $vendor, geocoder $geocoder)
    {
        if( $vendor == null )
        {
            throw new Exception('UAEFeedBaseMapper::construct - Require valid vendor');
        }

        if( $xml == null )
        {
            throw new Exception('UAEFeedBaseMapper::construct - Require valid XML Feed');
        }

        // Update Data
        $this->vendor       = $vendor;
        $this->xml          = $xml;
        $this->geocoder     = ( $geocoder == null ) ? new googleGeocoder( ) : $geocoder;
    }
    
}
?>
