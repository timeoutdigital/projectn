<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * @todo possibly implement skip download functionality if file was not changed
 *       since last download
 *
 * <b>Example</b>
 * <code>
 *
 * $ftpClient = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
 *
 * // if the files are not in the root node (default) the path can be specified (incl. leading and trailing slash
 * $ftpClient->setSourcePath( '/NOKIA/' );
 *
 * // if the file name is know, this is how to retrieve it. if the target file name should have a particular name
 * // this can be passed as second argument
 * $fileNameString = $ftpClient->fetchFile( 'tony_ed.xml' );
 *
 * // if a file name is changing a pattern can be specified and the latest (lastmod date of server) file matching
 * // against this pattern will be retrieved
 * $fileNameString = $ftpClient->fetchLatestFileByPattern( 'toc_[0-9]+.xml' );
 *
 * //there are some more options available (include, exclude patterns, etc, see method descriptions)
 *
 * </code>
 *
 */
class FTPClient
{
  /**
   *
   * @var ftpConnectionResource
   */
  private $connection;

  /**
   * @var array
   */
  private $excludePatterns = array( '^\.' );

  /**
   *
   * @var string
   */
  private $vendorImportPath;

  /**
   *
   * @var string
   */
  private $sourcePath = '/';

  /**
   *
   * @var boolean
   */
  private $testMode = false;

  /**
   *
   * @param string $host
   * @param string $username
   * @param string $password
   * @param string $targetPath
   * @param integer $port
   * @param integer $timeout
   * @param boolean $testMode
   */
  public function __construct( $host, $username='', $password='', $targetPath, $port=21, $timeout=90, $testMode = false )
  {
    
    $this->setTestMode( $testMode );
    $this->setVendorImportPath( $targetPath );
    $this->connection = ftp_connect( $host, $port, $timeout );

    if( !empty( $username ) && !empty( $password ) )
    {
      ftp_login( $this->connection, $username, $password );
    }

  }

  /**
   * switches test mode on and off
   *
   * @param boolean $mode
   *
   */
  public function setTestMode( $mode = true )
  {
   $this->testMode = $mode;
  }

  /**
   * sets (and creates if needed) the vendor import path
   *
   * @param string $vendorCity
   */
  public function setVendorImportPath( $vendorCity )
  {
    if ( $this->testMode )
    {
      $this->vendorImportPath = TO_TEST_ROOT_PATH . '/import/' . $vendorCity;
    }
    else
    {
      $this->vendorImportPath = sfConfig::get( 'sf_root_dir' ) . '/import/' . $vendorCity;
    }

    if( ! file_exists( $this->vendorImportPath ) )
    {
      mkdir( $this->vendorImportPath, 0777, true );
    }
  }  

  /**
   *
   * @param string $sourcePath
   */
  public function setSourcePath( $sourcePath )
  {
    $this->sourcePath = $sourcePath;
  }

  /**
   *
   * @return string
   */
  public function getSourcePath()
  {
    return $this->sourcePath;
  }

  /**
   * @todo implement this method
   */
  public function addExcludePattern( $pattern )
  {

  }

  /**
   * @todo implement this method
   */
  public function removeExcludePattern( $pattern )
  {
    
  }

  /**
   * Get exclude patterns
   *
   * @return array
   */
  public function getExcludePatterns()
  {
    return $this->excludePatterns;
  }

  public function getExcludePatternsString()
  {
    return '/' . implode( '|', $this->getExcludePatterns() ) . '/';
  }

  /**
   * GETS a file $srcFile and puts it into $targetDir
   * 
   * @param string $srcFile
   * @param mixed $targetFile (filename or false (which will make target = source)
   */
  public function fetchFile( $srcFile, $targetFile = false )
  {
    if ( $targetFile === false)
    {
      $targetFile = $srcFile;    
    }

    $targetPath = $this->vendorImportPath . '/' . $targetFile;

    if ( ftp_get( $this->connection, $targetPath, $this->sourcePath . $srcFile, FTP_BINARY ) )
    {
      return $targetPath;
    }

    throw new Exception( 'failed to fetch file' );
  }

  /**
   * fetches latest file matching to the pattern out of the directory
   *
   * @param string $srcFilePattern (Regular Expression)
   * @param string $targetFile
   *
   * @return boolean indicater if call was successful or not
   */
  public function fetchLatestFileByPattern( $srcFilePattern, $targetFile = false )
  {
    $dirListingArray = $this->fetchDirListing();

    $matchingFilesArray = array();

    foreach( $dirListingArray as $dirListingItem )
    {
      if( preg_match( '/' . $srcFilePattern . '/' , $dirListingItem[ 'filename' ] ) )
      {        
        $matchingFilesArray[ $dirListingItem[ 'filename' ] ] = $dirListingItem[ 'last_modified_time' ];
      }
    }

    if ( 0 < count( $matchingFilesArray) )
    {
      arsort( $matchingFilesArray );
      reset( $matchingFilesArray );
      $latestSrcFile = key( $matchingFilesArray );

      return $this->fetchFile( $latestSrcFile, $targetFile);
    }
    
    throw new Exception( 'failed to fetch file by pattern' );
  }

  /**
   * retrieves and parses a directory listing fetchDirListing
   *
   * @return array
   */
  public function fetchDirListing()
  {
    $parsedDirListingArray = array();

    $dirListingArray = $this->fetchRawDirListing();

    foreach( $dirListingArray as $dirItem )
    {
      $matches = array();
      preg_match( '/[\s]+((Jan|Feb|Mar|Apr|Mai|Jun|Jul|Aug|Sep|Nov|Dec)[\s]+([0-9]+){1}[\s]+([0-9]{2}:[0-9]{2}){1})[\s]+(.*+)$/', $dirItem, $matches );

      if ( isset( $matches[ 1 ] ) && isset( $matches[ 5 ] ) )
      {
        $infoArray = array();
        $infoArray[ 'filename' ] = $matches[ 5 ];
        $infoArray[ 'last_modified_time' ] = strtotime( $matches[ 1 ] );
        $infoArray[ 'last_modified_string' ] = date( 'Y-m-d H:i', strtotime( $matches[ 1 ] ) );
        $parsedDirListingArray[] = $infoArray;
      }
    }
    
    return $parsedDirListingArray;
  }

  /*
   * retrieves raw dir listing
   *
   * @return array
   */
  public function fetchRawDirListing()
  {
    return ftp_rawlist( $this->connection,  $this->sourcePath );
  }

  /**
   * Download all files from $srcPath to $targetPath which are newer
   *
   */
  public function fetchDirContents()
  {
    ftp_chdir( $this->connection, $this->sourcePath );
    $dirArray = ftp_nlist( $this->connection, '.' );

    foreach( $dirArray as $fileName )
    {
      if( !preg_match( $this->getExcludePatternsString(), $fileName ) )
      {
        $this->fetchFile( $fileName );
      }
    }
  }

  /**
   * Close the ftp connection
   */
  public function close()
  {
    ftp_close( $this->connection );
  }

  public function  __destruct()
  {
    $this->close();
  }
}
?>
