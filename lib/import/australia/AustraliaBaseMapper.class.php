<?php
/**
 * Australia base mapper
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
class australiaBaseMapper extends DataMapper
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

        $latestFileName = $this->_getTheLatestFileName( $ftpClient->fetchRawDirListing(), $params['ftp']['file'] );
        $saveToFileName = $params['ftp']['file'] . '.xml';

        // Download the File and Open as String contents into $contents
        $downloadedFileFullPath = $ftpClient->fetchFile( $latestFileName, $saveToFileName );
        $contents = file_get_contents( $downloadedFileFullPath );

        // Archive
        new FeedArchiver( $vendor, $contents, $params['type'] );

        // Load as SimpleXML
        $this->feed = simplexml_load_string( $contents );
        
    }

    /**
     * Sort by date attached filename and return the Latest filename using $xmlFileName
     * @param array $rawFtpListingOutput
     * @param string $xmlFileName
     * @return string
     */
    protected function _getTheLatestFileName( $rawFtpListingOutput, $xmlFileName )
    {

        // Sort FTP Rawoutput using Filename and Date attached tof ilename
        foreach ($rawFtpListingOutput as $fileListing)
        {
            $fileName = preg_replace( '/^.*?([-a-z0-9_]*.xml)$/', '$1', $fileListing );

            preg_match( '/^.*_([0-9\-]+)\.xml$/', $fileName, $matches );

            if( isset( $matches [1] ) )
            {
                $date = date( 'Y-m-d' ,strtotime($matches[1] ));
                $fileListSorted[ $date . ' ' .$fileName ] =   $fileListing;
            }

        }

        // Makesure we have sorted filenames
        if( count($fileListSorted) <= 0 )
        {
            throw new SydneyFTPBaseMapperException( 'Failed to Extract All File Names From Sydney FTP Directory Listing. FILE NAME FORMAT MIGHT BE CHANGED' );
        }

        ksort ( $fileListSorted );
        $fileListSorted = array_reverse( $fileListSorted );

        // Extract the Latest Filename for Given Type
        foreach( $fileListSorted as $fileNameSorted )
        {
            //get rid of the date / other info from ls command
            $filename = preg_replace( '/^.*?([-a-z0-9_]*.xml)$/', '$1', $fileNameSorted );

            if(strpos( $filename, $xmlFileName ) !== false )
            {
                return $filename;
            }
        }

        throw new SydneyFTPBaseMapperException( "Failed to get the Filename for : {$xmlFileName}" );
    }

    public function extractGeoCodesFromIframe( $iframeHTML )
    {
          if( stringTransform::mb_trim( $iframeHTML ) != '' )
          {
              $regEx = '/\&amp;ll=(.*?)\&amp;/i';
              preg_match( $regEx, $iframeHTML, $geocodes );

              if( is_array( $geocodes ) && count( $geocodes ) == 2 )
              {
                  $geolatLong = explode(',', $geocodes[1] );
                  if( count( $geolatLong ) == 2 )
                  {
                      $poi->applyFeedGeoCodesIfValid( $geolatLong[0], $geolatLong[1] );
                  }
              }
          }
    }
}

/**
 * Sydney FTP base mapper Exception class...
 */
class SydneyFTPBaseMapperException extends Exception{}