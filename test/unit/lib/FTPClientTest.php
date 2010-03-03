<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the FTP client
 *
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class FTPClientTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var FTPClient
   */
  protected $object;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
  protected function setUp()
  {
    //$this->object = new FTPClient( 'ftp.timeoutchicago.com', 'timeout', 'y6fv2LS8', 'chicago', 21, 90, true );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    unset( $this->object );
  }

  /**
   * testFetchAll
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
  public function testFetchAll()
  {
//    $file = TO_TEST_IMPORT_PATH  . '/chicago/toc_bc.xml';
//
//    if ( file_exists( $file ) )
//    {
//      unlink( $file );
//    }
//
//    $this->object->fetchDirContents();
//
//    $this->assertFileExists( TO_TEST_IMPORT_PATH  . '/chicago/toc_bc.xml' );
//    $this->assertFileExists( TO_TEST_IMPORT_PATH  . '/chicago/toc_ed.xml' );
//    $this->assertFileExists( TO_TEST_IMPORT_PATH  . '/chicago/toc_leo.xml' );
//    $this->assertFileExists( TO_TEST_IMPORT_PATH  . '/chicago/xffd_TOChicago_*.xml' );

    $this->markTestIncomplete( 'test incomplete (due to instable ftp connection)' );
  }

  /**
   * testFetchFile
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
  public function testFetchFile()
  {
//    $file = TO_TEST_IMPORT_PATH  . '/chicago/toc_bc.xml';
//
//    if ( file_exists( $file ) )
//    {
//      unlink( $file );
//    }
//
//    $this->object->fetchFile( 'toc_bc.xml' );
//
//    $this->assertFileExists( $file );

    $this->markTestIncomplete( 'test incomplete (due to instable ftp connection)' );
  }

  /**
   * testSetVendorImportPath
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
  public function testSetVendorImportPath()
  {
//    $dir = TO_TEST_IMPORT_PATH  . '/test';
//
//    if ( file_exists( $dir ) )
//    {
//      rmdir( $dir );
//    }
//
//    $this->object->setVendorImportPath( 'test' );
//
//    $this->assertFileExists( $dir );

    $this->markTestIncomplete( 'test incomplete (due to instable ftp connection)' );
  }

  /**
   * testFetchDirListing
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
  public function testFetchDirListing()
  {
//    $dirListingArray = array();
//    $dirListingArray[] = '-rw-rw----  1 timeout  staff   1923887 Feb  1 11:30 toc_bc.xml';
//    $dirListingArray[] = '-rw-rw----  1 timeout  staff   4127224 Feb  1 10:55 toc_ed.xml';
//    $dirListingArray[] = '-rw-rw----  1 timeout  staff  14904334 Jan 27 00:02 toc_leo.xml';
//    $dirListingArray[] = '-rwxrwx--x  1 timeout  staff   3463422 Feb  1 03:47 xffd_TOChicago_20100201.xml';
//
//    $stubFTPClient = $this->getMock( 'FTPClient' );
//    $stubFTPClient->expects( $this->any() )
//                     ->method( 'fetchRawDirListing' )
//                     ->will( $this->returnValue( $dirListingArray ) );
//
//    $dirListing = $this->object->fetchDirListing();
//
//    $this->assertEquals( 'toc_leo.xml2', $dirListing[ 3 ][ 'filename' ] );
//    $this->assertEquals( '2010-01-27 00:02', $dirListing[ 3 ][ 'last_modified_string' ] );
//    $this->assertEquals( strtotime( '2010-01-27 00:02' ), $dirListing[ 3 ][ 'last_modified_time' ] );
    
    $this->markTestIncomplete( 'test incomplete (due to instable ftp connection)' );
  }

  /*
   * testFetchLatestFileByPattern
   *
   * @todo sort out ftp issues to get test back up in operation
   *
   */
   public function testFetchLatestFileByPattern()
   {
//     $file = TO_TEST_IMPORT_PATH  . '/chicago/toc_ed.xml';
//
//     if ( file_exists( $file ) )
//     {
//       unlink( $file );
//     }
//
//     $this->object->fetchLatestFileByPattern( '_ed.xml' );
//
//     $this->assertFileExists( $file );

     $this->markTestIncomplete( 'test incomplete (due to instable ftp connection)' );
   }

}
?>
