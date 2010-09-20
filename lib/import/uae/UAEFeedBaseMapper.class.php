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
    protected $vendor_id;
    protected $xml;
    protected $geocoder;

    public function  __construct( Doctrine_Record $vendor, SimpleXMLElement $xml, geocoder $geocoder = null)
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
        $this->vendor_id    = $vendor['id'];
        $this->xml          = $xml;
        $this->geocoder     = ( $geocoder == null ) ? new googleGeocoder( ) : $geocoder;
    }
    
}
?>
