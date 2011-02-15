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
    private $s3cmd = 's3cmd';

    public function __construct( )
    {
        // Set nagios config file location (for nagios and www-root users)
        if( !file_exists( '~/.s3cfg' ) ) $this->s3cmd .= ( ' -c ' . sfConfig::get( 'sf_root_dir' ) . '/lib/task/nagios/.s3cfg' );
        
        // Note this requires the s3cmd utility 0.9.9.91 or greater. Available on prod, but maybe not on your comp.
        if( strlen( trim( shell_exec( "{$this->s3cmd} --version 2> /dev/null" ) ) ) < 1 )
          throw new Exception( "Please Install & Configure the latest version of s3cmd. An installation script is available in /n/scripts" );

    }

    public function getListOfMediaAvailableOnAmazon( $vendorCity, $recordClass )
    {
        $vendorCity   = strtolower( $vendorCity );
        $recordClass  = strtolower( $recordClass );

        $imageList    = trim( shell_exec( "{$this->s3cmd} ls s3://projectn/{$vendorCity}/{$recordClass}/media/ | cut exitd'/' -f7" ) );

        return (array) explode( "\n", $imageList );
    }

    public function fileExists( $path )
    {
        if( substr( $path, 0, 1 ) !== '/' ) throw new s3cmdException( 'You must provide a valid absolute path beginning with a /' );
        return (bool) strlen( trim( shell_exec( "{$this->s3cmd} ls s3:/$path 2> /dev/null" ) ) ) > 0;
    }

    public function ls( $path )
    {
        if( substr( $path, 0, 1 ) !== '/' ) throw new s3cmdException( 'You must provide a valid absolute path beginning with a /' );
        exec( "{$this->s3cmd} ls s3:/$path", $result, $status );
        return (array) $result;
    }

    public function info( $path )
    {
        if( !$this->fileExists( $path ) ) return false; // s3cmd has a bug where it hangs if you ask for info on a non existant file.

        $result = array();
        exec( "{$this->s3cmd} info s3:/$path", $result, $status );

        array_shift( $result );
        foreach( $result as $k => $v )
        {
            $findColon = strpos( $v, ':' );
            if( is_numeric( $findColon ) ) $result[ trim( substr( $v, 0, $findColon ) ) ] = trim( substr( $v, $findColon ), ': ' );
            unset( $result[ $k ] );
        }

        return (array) $result;
    }

    /**
     * This will query s3 for export files and return anything
     * have the prefix "exports_" file name as Array KEY and the MD5 of that file as value
     * @return array
     */
    public function getListOfExportArchives()
    {
        $exports = trim(shell_exec( "{$this->s3cmd} ls --list-md5 s3://timeout-projectn-backups/export/ | grep 'exports_' | awk -F' ' '{print $4, \"~\", $5}'") );
        $exports_array = explode( "\n", $exports );
        
        $return_list = array(); // This will store [filename] => MD5_SUM
        foreach( $exports_array as $export )
        {
            $split = explode( '~', $export );

            if( count($split) != 2 ) continue; // Only continue IF WE have two set of data
            
            $filename = trim( str_replace( 's3://timeout-projectn-backups/export/', '', $split[1] ) );

            if( strlen($filename) !=  20 ) continue; // Careful on File names, It shouls ALWAYS be 20 CHAR long

            $md5 = trim($split[0]);

            $return_list[ $filename ] = $md5; // add to the return list
        }

        return $return_list;

    }
}

class s3cmdException extends Exception {}