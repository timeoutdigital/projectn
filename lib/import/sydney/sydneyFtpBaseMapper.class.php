<?php
/**
 * Sydney base mapper
 *
 * @package projectn
 * @subpackage sydney.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevakumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class sydneyFtpBaseMapper extends DataMapper
{
    protected $vendor;
    protected $feed;

    /**
     * Sydney FTP Base mapper constructor
     * @param Vendor $vendor
     * @param array $params 
     */
    public function  __construct( Vendor $vendor, array $params )
    {

        $this->_validateConstructorParams( $vendor, $params );  // validate
        $this->_loadXMLFromFTP( $vendor, $params ); // Load XML

        // Set class variables value
        $this->vendor = $vendor;
        
    }

    /**
     * Validate All required parameters are passed
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateConstructorParams( $vendor, $params )
    {
        if( !$vendor || !isset($vendor['id']) )
        {
            throw new SydneyFTPBaseMapperException( 'Invalid vendor in Parameter' );
        }

        if( !is_array($params) || count( $params ) <= 0 || !isset( $params['ftp'] ) || !isset( $params['ftp']['classname'] ) )
        {
            throw new SydneyFTPBaseMapperException( 'Invalid $params in Parameter' );
        }
    }

    /**
     * Download file from FTP and Parse it as SimpleXML
     * @param Vendor $vendor
     * @param array $params
     */
    private function _loadXMLFromFTP( $vendor, $params )
    {
        // Create FTP client [ src, username, password, target ]
        $ftpClient = new $params['ftp']['classname']( $params['ftp']['src'], $params['ftp']['username'], $params['ftp']['password'], $vendor['city'] );
        $ftpClient->setSourcePath( $params['ftp']['dir'] );

        $downloadedFileName = $ftpClient->fetchLatestFileByPattern( $params['ftp']['file'] );

        if( !file_exists( $downloadedFileName ) )
        {
            throw new SydneyFTPBaseMapperException( 'FTP download failed, invalid source path returned: ' . $downloadedFileName );
        }

        $contents = file_get_contents( $downloadedFileName );

        // Archive
        new FeedArchiver( $vendor, $contents, $params['type'] );

        // Load as SimpleXML
        $this->feed = simplexml_load_string( $contents );
        
    }
    
}

/**
 * Sydney FTP base mapper Exception class...
 */
class SydneyFTPBaseMapperException extends Exception{}