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
  
  private $_logEndLineRegex = '';

  
  protected function setUp() { 
      
      $this->_logStartLineRegex = <<<EOF
/\>\> START\:    testTaskLogger [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}/
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

      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[2], 'end string missmatch' );
      $this->assertEquals( $testDataArray[1], $outputArray[1], 'param string missmatch' );
  }



  public function testTaskLoggerSuccess()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=success' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerSuccess.log' ) );

      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[7], 'end string missmatch' );

      $outputWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $outputArray, 1, count( $outputArray )-3 ) );
      $testDataWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $testDataArray, 1, count( $testDataArray )-3 ) );

      $this->assertEquals( $testDataWithoutStartAndEndLine, $outputWithoutStartAndEndLine, 'log body did not match' );
  }


  public function testTaskLoggerErrorNotice()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-notice 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorNotice.log' ) );

      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[4], 'end string missmatch' );

      $outputWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $outputArray, 1, count( $outputArray )-3 ) );
      $testDataWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $testDataArray, 1, count( $testDataArray )-3 ) );

      $this->assertEquals( $testDataWithoutStartAndEndLine, $outputWithoutStartAndEndLine, 'log body did not match' );
  }

  public function testTaskLoggerErrorWarning()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-warning 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorWarning.log' ) );

      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );
      $this->assertRegExp( $this->_logEndLineRegex, $outputArray[4], 'end string missmatch' );

      $outputWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $outputArray, 1, count( $outputArray )-3 ) );
      $testDataWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $testDataArray, 1, count( $testDataArray )-3 ) );

      $this->assertEquals( $testDataWithoutStartAndEndLine, $outputWithoutStartAndEndLine, 'log body did not match' );
  }

  public function testTaskLoggerErrorError()
  {
      $outputArray = explode( PHP_EOL, shell_exec( './symfony projectn-mock:testTaskLogger --type=error-error 2> /dev/null' ) );
      $testDataArray = explode( PHP_EOL, file_get_contents( TO_TEST_DATA_PATH . '/logger/testTaskLoggerErrorError.log' ) );

      $this->assertRegExp( $this->_logStartLineRegex, $outputArray[0], 'start string missmatch' );

      $this->assertEquals( 0, preg_match( $this->_logEndLineRegex, $outputArray[3] ), 'end should be missing' );

      $outputWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $outputArray, 1 ) );
      $testDataWithoutStartAndEndLine = implode( PHP_EOL, array_splice( $testDataArray, 1 ) );

      $this->assertEquals( $testDataWithoutStartAndEndLine, $outputWithoutStartAndEndLine, 'log body did not match' );
  }

}
?>
