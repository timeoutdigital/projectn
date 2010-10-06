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

        return (array) explode( "\n", $imageList );
    }

    public function fileExists( $path )
    {
        if( substr( $path, 0, 1 ) !== '/' ) throw new s3cmdException( 'You must provide a valid absolute path beginning with a /' );
        return (bool) strlen( trim( shell_exec( "s3cmd ls s3:/$path 2> /dev/null" ) ) ) > 0;
    }

    public function ls( $path )
    {
        if( substr( $path, 0, 1 ) !== '/' ) throw new s3cmdException( 'You must provide a valid absolute path beginning with a /' );
        exec( "s3cmd ls s3:/$path", $result, $status );
        return (array) $result;
    }

    public function info( $path )
    {
        if( !$this->fileExists( $path ) ) return false; // s3cmd has a bug where it hangs if you ask for info on a non existant file.

        $result = array();
        exec( "s3cmd info s3:/$path", $result, $status );

        array_shift( $result );
        foreach( $result as $k => $v )
        {
            $findColon = strpos( $v, ':' );
            if( is_numeric( $findColon ) ) $result[ trim( substr( $v, 0, $findColon ) ) ] = trim( substr( $v, $findColon ), ': ' );
            unset( $result[ $k ] );
        }

        return (array) $result;
    }
}

class s3cmdException extends Exception {}