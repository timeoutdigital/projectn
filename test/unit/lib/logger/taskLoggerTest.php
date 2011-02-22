<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../bootstrap.php';

require_once dirname(__FILE__).'/../../../../lib/stringTransform.class.php';


/**
 * Test class for taskLogger
 *
 * @package test
 * @subpackage lib.unit.lib.logger
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 
 *
 * @version 1.0.1
 *
 *
 */
class taskLoggerTest extends PHPUnit_Framework_TestCase {


  
  private $_logStartLineRegex = '';

  private $_logFileLineRegex = '';
  
  private $_logEndLineRegex = '';

  
  protected function setUp() { 
      
      $this->_logStartLineRegex = <<<EOF
/\>\> START\:    testTaskLogger [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}/
EOF;

      $this->_logFileLineRegex = <<<EOF
/\>\> File\:     \/.+\/testTaskLoggerTask\.class.php/
EOF;
      
      $this->_logEndLineRegex = <<<EOF
/\>\> END\:      testTaskLogger   [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}/
EOF;

  }

  protected function tearDown() { }


  public function testTaskLoggerEmptyLog()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerEmptyLog.log' ) );

      $this->assertEquals( count( $outputArray ), count( $testDataArray ), 'test data missmatch (different file line count)' );
      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logFileLineRegex, $outputArray[1], 'file string missmatch' );
      $this->assertEquals( $testDataArray[2], $outputArray[2], 'param string missmatch' );

      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[ count($outputArray)-2 ], 'end string missmatch' );
  }

  public function testTaskLoggerSuccess()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=success' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerSuccess.log' ) );

      $this->assertEquals( count( $outputArray ), count( $testDataArray ), 'test data missmatch (different file line count)' );
      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logFileLineRegex, $outputArray[1], 'file string missmatch' );
      $this->assertEquals( $testDataArray[2], $outputArray[2], 'param string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[ count($outputArray)-2 ], 'end string missmatch' );

      $outputLogBody = implode( PHP_EOL, array_splice( $outputArray, 3, count( $outputArray )-5 ) );
      $testLogBody = implode( PHP_EOL, array_splice( $testDataArray, 3, count( $testDataArray )-5 ) );

      $this->assertEquals( $testLogBody, $outputLogBody, 'log body did not match' );
  }

  public function testTaskLoggerErrorNotice()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-notice 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorNotice.log' ) );

      $this->assertEquals( count( $outputArray ), count( $testDataArray ), 'test data missmatch (different file line count)' );
      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logFileLineRegex, $outputArray[1], 'file string missmatch' );
      $this->assertEquals( $testDataArray[2], $outputArray[2], 'param string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[ count($outputArray)-2 ], 'end string missmatch' );

      $outputLogBody = implode( PHP_EOL, array_splice( $outputArray, 3, count( $outputArray )-5 ) );
      $testLogBody = implode( PHP_EOL, array_splice( $testDataArray, 3, count( $testDataArray )-5 ) );

      //replace local pathes with generic ones to match test data
      $outputLogBody = preg_replace( '/triggered error in \/.+\/projectn\//', 'triggered error in /absolute/path/projectn/', $outputLogBody );

      $this->assertEquals( $testLogBody, $outputLogBody, 'log body did not match' );
  }

  public function testTaskLoggerErrorWarning()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-warning 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorWarning.log' ) );

      $this->assertEquals( count( $outputArray ), count( $testDataArray ), 'test data missmatch (different file line count)' );
      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logFileLineRegex, $outputArray[1], 'file string missmatch' );
      $this->assertEquals( $testDataArray[2], $outputArray[2], 'param string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[ count($outputArray)-2 ], 'end string missmatch' );

      $outputLogBody = implode( PHP_EOL, array_splice( $outputArray, 3, count( $outputArray )-5 ) );
      $testLogBody = implode( PHP_EOL, array_splice( $testDataArray, 3, count( $testDataArray )-5 ) );

      //replace local pathes with generic ones to match test data
      $outputLogBody = preg_replace( '/triggered error in \/.+\/projectn\//', 'triggered error in /absolute/path/projectn/', $outputLogBody );

      $this->assertEquals( $testLogBody, $outputLogBody, 'log body did not match' );
  }

  public function testTaskLoggerErrorError()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-error 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorError.log' ) );

      //$this->assertEquals( count( $outputArray ), count( $testDataArray ), 'test data missmatch (different file line count)' );
      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logFileLineRegex, $outputArray[1], 'file string missmatch' );
      $this->assertEquals( $testDataArray[2], $outputArray[2], 'param string missmatch' );
      
      $this->assertEquals( 0, preg_match( $this->_logEndLineRegex, $outputArray[ count($outputArray)-2 ] ), 'end should be missing' );

      $outputLogBody = implode( PHP_EOL, array_splice( $outputArray, 3, 2 ) );
      $testLogBody = implode( PHP_EOL, array_splice( $testDataArray, 3, 2 ) );

      //replace local pathes with generic ones to match test data
      $outputLogBody = preg_replace( '/triggered error in \/.+\/projectn\//', 'triggered error in /absolute/path/projectn/', $outputLogBody );

      $this->assertEquals( $testLogBody, $outputLogBody, 'log body did not match' );
  }
}
?>
