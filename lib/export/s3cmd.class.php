<?php
/**
 *
 * @package projectn
 * @subpackage export.lib
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */

class s3cmd
{
    public function __construct( )
    {
        // Note this requires the s3cmd utility 0.9.9.91 or greater. Available on prod, but maybe not on your comp.
        if( strlen( trim( shell_exec( 's3cmd --version 2> /dev/null' ) ) ) < 1 )
          throw new Exception( "Please Install & Configure the latest version of s3cmd. An installation script is available in /n/scripts" );

    }

    public function getListOfMediaAvailableOnAmazon( $vendorCity, $recordClass )
    {
        $vendorCity   = strtolower( $vendorCity );
        $recordClass  = strtolower( $recordClass );

        $imageList    = trim( shell_exec( "s3cmd ls s3://projectn/{$vendorCity}/{$recordClass}/media/ | cut -d'/' -f7" ) );

        return explode( "\n", $imageList );
    }
}