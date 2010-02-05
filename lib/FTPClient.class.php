<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
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
   * @param string $host
   * @param string $username
   * @param string $password
   * @param integer $port
   * @param integer $timeout
   */
  public function __construct( $host, $username='', $password='', $port=21, $timeout=90 )
  {
    $this->connection = ftp_connect( $host, $port, $timeout );

    if( !empty( $username ) && !empty( $password ) )
    {
      ftp_login( $this->connection, $username, $password );
    }
  }

  /**
   * @todo implement this method
   */
  public function addExcludePatter( $pattern )
  {

  }

  /**
   * @todo implement this method
   */
  public function removeExcludePatter( $pattern )
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
   */
  public function fetchFile( $srcFile, $targetFile )
  {
    ftp_get( $this->connection, $targetFile, $srcFile, FTP_BINARY );
  }

  /**
   * Download all files from $srcPath to $targetPath which are newer
   *
   * @param string $srcPath
   * @param string $targetPath
   */
  public function fetchDirContents( $srcPath, $targetPath )
  {
    ftp_chdir($this->connection, $srcPath);
    $dirArray = ftp_nlist( $this->connection, '.' );
    
    foreach( $dirArray as $fileName )
    {
      if( !preg_match( $this->getExcludePatternsString(), $fileName ) )
      {
        var_dump( $fileName );
        ftp_get( $this->connection, $targetPath . '/' . $fileName, $fileName, FTP_BINARY );
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
