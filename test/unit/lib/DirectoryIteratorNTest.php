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
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DirectoryIteratorNTest extends PHPUnit_Framework_TestCase
{

  protected $object;

  protected $baseDir;


  protected function setUp()
  {
      $this->baseDir = TO_TEST_DATA_PATH .DIRECTORY_SEPARATOR . 'DirectoryIteratorNTest' . DIRECTORY_SEPARATOR;
      
      mkdir( $this->baseDir );
      mkdir( $this->baseDir . 'prefixed_dir');
      mkdir( $this->baseDir . 'dir');
      touch( $this->baseDir . 'file.txt');
      touch( $this->baseDir . 'file.xml');
      touch( $this->baseDir . 'prefixed_file.xml');
  }

  protected function tearDown()
  {
    //delete the our test folder structure
    exec( 'rm -rf ' .  $this->baseDir );
  }

  //final public static function iterate( $dir = ".", $which = self::DIR_ALL, $extension = '', $prefix = '' )

  public function testDefaultCall()
  {
      $rootDirectoryContent = DirectoryIteratorN::iterate();
      $directoryContent = DirectoryIteratorN::iterate( $this->baseDir );

      $this->assertNotEquals( $rootDirectoryContent, $directoryContent, 'failed to change directory' );
      $this->assertEquals( 5, count( $directoryContent ), 'failed to change directory' );
  }


  public function testTypeOptions()
  {
      $directoryContentFiles = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FILES );
      $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS );

      $this->assertEquals( 3, count( $directoryContentFiles ), 'failed to match file count' );
      $this->assertEquals( 2, count( $directoryContentDirs ), 'failed to match dir count' );
  }

  public function testExtentionOption()
  {
      $directoryContentFiles = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FILES, 'xml' );
      $this->assertEquals( 2, count( $directoryContentFiles ), 'failed to match file count' );
  }

  public function testInvalidParameterCombination()
  {
      $this->setExpectedException('Exception');
      $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS, 'xml' );
      $this->setExpectedException('Exception');
      $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS, 'xml', 'prefixed_' );
  }

  public function testPrefixOption()
  {
    // add another file, to make sure the prefix string is only taken into account if at the beginning of the file name
    touch( $this->baseDir . 'notprefixed_file.xml');

    $directoryContentFiles = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FILES, '', 'prefixed' );
    $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS, '', 'prefixed' );
    $directoryContentAll = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_ALL, '', 'prefixed' );

    $this->assertEquals( 1, count( $directoryContentFiles ), 'failed to match prefixed file count' );
    $this->assertEquals( 1, count( $directoryContentDirs ), 'failed to match prefixed dir count' );
    $this->assertEquals( 2, count( $directoryContentAll ), 'failed to match prefix file/dir count' );
  }

  public function testReturnAbsolutPath()
  {
    $directoryContentFiles = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FILES, '', '' );
    $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS, '', '' );
    $directoryContentAll = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_ALL, '', '' );

    $this->assertEquals( substr_count( $directoryContentFiles[0], '/' ), 0, 'slashes found in the path filename' );
    $this->assertEquals( substr_count( $directoryContentDirs[0], '/' ), 0, 'slashes found in the path filename' );
    $this->assertEquals( substr_count( $directoryContentAll[0], '/' ), 0, 'slashes found in the path filename' );

    $directoryContentFiles = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FILES, '', '', true );
    $directoryContentDirs = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_FOLDERS, '', '', true );
    $directoryContentAll = DirectoryIteratorN::iterate( $this->baseDir, DirectoryIteratorN::DIR_ALL, '', '', true );
    
    $this->assertGreaterThan( 0, substr_count( $directoryContentFiles[0], '/' ), 'no slashes found in the path filename' );
    $this->assertGreaterThan( 0, substr_count( $directoryContentDirs[0], '/' ), 'no slashes found in the path filename' );
    $this->assertGreaterThan( 0, substr_count( $directoryContentAll[0], '/' ), 'no slashes found in the path filename' );;
  }

}
?>
