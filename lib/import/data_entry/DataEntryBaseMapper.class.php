<?php
/* 
 * Common for Data Entry
 * Autor: Rajeevan kumarathasan
 */

class DataEntryBaseMapper extends DataMapper
{
    /**
    *
    * @var projectNDataMapperHelper
    */
    protected $dataMapperHelper;

    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    protected function addAsString( $object, $field, $xmlNode )
    {
        if( !isset($xmlNode) || !$xmlNode )
            return false;

        $object[$field] = (string) $xmlNode;
        
        return true;
    }
}
?>
