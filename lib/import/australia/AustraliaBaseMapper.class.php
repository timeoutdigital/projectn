<?php
/**
 * Australia base mapper
 *
 * @package projectn
 * @subpackage australia.import.lib.unit
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
     * Australia Base mapper constructor
     * @param Vendor $vendor
     * @param array $params 
     */
    public function  __construct( Vendor $vendor, array $params )
    {
        $this->validateContructorParamsAndChooseDataSourceAndLoadXML( $vendor, $params ); // Refactor this once datasources have been abstracted.
        
        // Set class variables value
        $this->vendor = $vendor;
        
    }

    /**
     * Switch data sources (sydney=ftp, melbourne=feed)
     * This is a temporary measure, please refactor or remove once datasources have been abstracted.
     *
     * @param Vendor $vendor
     * @param array $params
     */
    public function validateContructorParamsAndChooseDataSourceAndLoadXML( Vendor $vendor, array $params )
    {
        switch( $vendor['city'] )
        {
            case 'sydney'       : $dataSourceClass = 'ftp'; break;
            case 'melbourne'    : $dataSourceClass = 'curl'; break;
            default : throw new AustraliaBaseMapperException( "Invalid city : {$vendor['city']}" );
        }

        $this->_validateConstructorParams( $vendor, $params, $dataSourceClass );  // validate

        switch( $dataSourceClass )
        {
            case 'ftp'  : $this->_loadXMLFromFTP( $vendor, $params ); break; // Load XML via FTP
            case 'curl' : $this->_loadXMLFromFeed( $vendor, $params ); break; // Load XML via Feed
            default : throw new AustraliaBaseMapperException( "Invalid data source class specified: {$dataSourceClass}" );
        }
    }

    /**
     * Validate All required parameters are passed
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateConstructorParams( $vendor, $params, $dataSourceClass )
    {
        if( !$vendor || !isset($vendor['id']) )
        {
            throw new AustraliaBaseMapperException( 'Invalid vendor in Parameter' );
        }

        if( !is_array( $params ) || count( $params ) <= 0 || !isset( $params[ $dataSourceClass ] ) || !isset( $params[ $dataSourceClass ]['classname'] ) )
        {
            throw new AustraliaBaseMapperException( 'Invalid $params in Parameter' );
        }
    }

    /**
     * Download file from FTP and Parse it as SimpleXML
     * @param Vendor $vendor
     * @param array $params
     */
    protected function _loadXMLFromFTP( $vendor, $params )
    {
        // Create FTP client [ src, username, password, target ]
        $ftpClient = new $params['ftp']['classname']( $params['ftp']['src'], $params['ftp']['username'], $params['ftp']['password'], $vendor['city'] );
        //$ftpClient->setSourcePath( $params['ftp']['dir'] );

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
     * Download file from Feed and Parse it as SimpleXML
     * @param Vendor $vendor
     * @param array $params
     */
    protected function _loadXMLFromFeed( $vendor, $params )
    {
        // Get the Feed
        $curl = new $params['curl']['classname']( $params['curl']['src'] );
        $curl->exec();

        // Archive
        new FeedArchiver( $vendor, $curl->getResponse(), $params['type'] );

        // Load as SimpleXML
        $this->feed = simplexml_load_string( $curl->getResponse() );
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
            throw new AustraliaBaseMapperException( 'Failed to Extract All File Names From Sydney FTP Directory Listing. FILE NAME FORMAT MIGHT BE CHANGED' );
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

        throw new AustraliaBaseMapperException( "Failed to get the Filename for : {$xmlFileName}" );
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

    protected function extractDateTime( $dateString )
    {
        if ( empty( $dateString ) )
            return false;

        $dateTime = DateTime::createFromFormat( 'd/m/Y g:i:s A', $dateString );

        /* Try and fix time if it is provided as ( 24hr format with AM/PM ), DateTime doesn't like that very much */
        if( $dateTime === false )
        {
            $dateTime = DateTime::createFromFormat( 'd/m/Y G:i:s', str_replace( array( ' AM', ' PM' ) , '', $dateString ) );
        }

        return $dateTime;
    }

    protected function extractDate( $dateString, $dateOnly = false )
    {
        $date = $this->extractDateTime( $dateString );
        if( $date === false ) return null;
        
        return ( $dateOnly == true ) ? $date->format( 'Y-m-d' ) : $date->format( 'Y-m-d H:i:s' );
    }
}

/**
 * Sydney FTP base mapper Exception class...
 */
class AustraliaBaseMapperException extends Exception{}