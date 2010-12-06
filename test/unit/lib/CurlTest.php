<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the curl importer
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
class CurlTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Curl
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {    
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * @todo Implement testGetResponse().
   */
  public function testGetResponse()
  {
    $this->object = new Curl( 'http://www.google.co.uk/search', array( 'q' => 'wave', 'foo' => 'bar' ) );
    $this->object->exec();
    $this->assertRegExp('/wave/', $this->object->getResponse() );
  }

  /**
   * @todo Implement testGetParametersString().
   */
  public function testGetParametersString()
  {
    $this->object = new Curl( 'http://www.google.co.uk/search', array( 'q' => 'wave', 'foo' => 'bar' ) );
    $this->assertEquals('q=wave&foo=bar', $this->object->getParametersString() );
  }


  /**
   * test if a path is successfully created and set
   */
  public function testSetStorePath()
  {
    $testPath = TO_TEST_ROOT_PATH . '/import/ny/images';

    $this->object = new Curl( 'http://www.google.co.uk/search', array( 'q' => 'wave', 'foo' => 'bar' ) );
    $this->object->setStorePath( $testPath );

    $this->assertFileExists( $testPath );
    $this->assertEquals( $testPath, $this->object->getStorePath() );
  }

  /**
   * tests if the store response is saved successfully as file
   */
  public function testStoreResponse()
  {
      $this->object = new Curl( 'http://www.google.co.uk/search', array( 'q' => 'wave', 'foo' => 'bar' ) );
      $this->object->exec();

      $testFile = TO_TEST_ROOT_PATH . '/import/ny/images/test.txt';

      //clean out the already existing files first
      if ( file_exists( $testFile ) )  unlink( $testFile );

      $this->object->storeResponse( $testFile );

      $this->assertFileExists( $testFile );

      $testFile = 'test2.txt';
      $testPath = TO_TEST_ROOT_PATH . '/import/ny/images';
      $testFullPath = $testPath . '/' . $testFile;

      //clean out the already existing files first
      if ( file_exists( $testFullPath ) )  unlink( $testFullPath );

      $this->object->setStorePath( $testPath );
      $this->object->storeResponse( $testFile );

      $this->assertFileExists( $testFullPath );
  }

  /**
   * test if exception is trown on a request with an invalid url
   */
  public function testIfErrorMessageIsReturnedIfNoHTTP200IsReceived()
  {
      $this->setExpectedException( 'Exception' );
      $this->object = new Curl( 'http://somewrongurl' );
      $this->object->exec();
  }
  
  /**
   * test if header is successfully returned
   */
  public function testIfHeaderSuccesfullyReturned()
  {
      $header = <<<EOF
HTTP/1.1 200 OK
Date: Tue, 23 Feb 2010 12:16:42 GMT
Server: Apache/2.2.3 (CentOS)
Last-Modified: Thu, 04 Feb 2010 08:45:43 GMT
ETag: "5a5c066-46f1e-47ec25c3b7bc0"
Accept-Ranges: bytes
Content-Length: 290590
Connection: close
Content-Type: image/jpeg

EOF;

      $this->stubCurl = $this->getMock( 'Curl', array( 'getHeader' ), array( 'http://www.toimg.net/travel/images/logos/home.gif' ) );
      $this->stubCurl->expects( $this->any() )->method( 'getHeader' )->will( $this->returnValue( $header ) );

      $this->assertEquals( 'Thu, 04 Feb 2010 08:45:43 GMT', $this->stubCurl->getHeaderField( 'Last-Modified' ) );
      $this->assertEquals( 'image/jpeg', $this->stubCurl->getContentType() );
  }


  public function testDownloadTo()
  {
      $testFile = TO_TEST_ROOT_PATH . '/import/test/images/test.jpg';

      $this->object = new Curl( 'http://www.toimg.net/travel/images/logos/home.gif' );
      $this->object->exec();
      $this->object->downloadTo( $testFile );
      $curlInfo = $this->object->getCurlInfo();
      $lastModified = $this->object->getLastModified() ;

      $this->assertEquals( '200', $curlInfo[ 'http_code' ] );      

      $this->object = new Curl( 'http://www.toimg.net/travel/images/logos/home.gif' );
      $this->object->exec();
      $this->object->downloadTo( $testFile, $lastModified );
      $curlInfo = $this->object->getCurlInfo();
      $lastModified = $this->object->getLastModified() ;

      $this->assertEquals( '304', $curlInfo[ 'http_code' ] );
  }

  public function testInvalidParametersGetMethodInvalidValue()
  {
      $params = array( 'valid' => 'true', 'invalid' => array( 'a' => 'b' ) );

      $this->setExpectedException( 'CurlException' );
      $curl = new Curl( 'http://localhost', $params );
      
  }
}