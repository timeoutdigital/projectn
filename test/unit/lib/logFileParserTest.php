<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * test logFileParser
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class logFileParserTest  extends PHPUnit_Framework_TestCase
{

    protected $logFileParser;


    protected function setUp() {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->logFileParser = new logFileParser();
    }

    protected function tearDown() {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testParams()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerEmptyLog.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( 'testTaskLogger', $logTask[ 'name' ] );
        $this->assertEquals( '/absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php', $logTask[ 'command' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );

        $this->assertEquals( 5, $logTask[ 'LogTaskParam' ]->count() );
        $this->assertEquals( 'type', $logTask[ 'LogTaskParam' ][ 0 ][ 'name' ] );
        $this->assertEquals( '', $logTask[ 'LogTaskParam' ][ 0 ][ 'value' ] );
        $this->assertEquals( 'env', $logTask[ 'LogTaskParam' ][ 1 ][ 'name' ] );
        $this->assertEquals( 'prod', $logTask[ 'LogTaskParam' ][ 1 ][ 'value' ] );
        $this->assertEquals( 'connection', $logTask[ 'LogTaskParam' ][ 2 ][ 'name' ] );
        $this->assertEquals( 'project_n', $logTask[ 'LogTaskParam' ][ 2 ][ 'value' ] );
        $this->assertEquals( 'application', $logTask[ 'LogTaskParam' ][ 3 ][ 'name' ] );
        $this->assertEquals( 'backend', $logTask[ 'LogTaskParam' ][ 3 ][ 'value' ] );
    }

    public function testNoParamsNoFile()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerEmptyNoFilesNoParamLog.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( 'testTaskLogger', $logTask[ 'name' ] );
        $this->assertEquals( '', $logTask[ 'command' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );

        $this->assertEquals( 0, $logTask[ 'LogTaskParam' ]->count() );
    }
    
    public function testSetVendor()
    {
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'id' => 999, 'city' => 'log test vendor' ) );

        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerEmptyLog.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( '999', $logTask[ 'vendor_id' ] );
        $this->assertEquals( 'testTaskLogger', $logTask[ 'name' ] );
        $this->assertEquals( '/absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php', $logTask[ 'command' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );
    }

    public function testNotUniqueVendor()
    {
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'id' => 998, 'city' => 'log test vendor 1' ) );
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'id' => 999, 'city' => 'log test vendor 1' ) );

        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerMulitpleVendors.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 4, Doctrine::getTable('LogTask')->count() );
        //we expect an empty vendor due to not unique vendor lookup
        $this->assertEquals( '', $logTask[ 'vendor_id' ] );
        $this->assertEquals( 'testTaskLogger', $logTask[ 'name' ] );
        $this->assertEquals( '/absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php', $logTask[ 'command' ] );
        $this->assertEquals( '2011-07-02 16:01:54', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:01:54', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );
    }

    public function testEmptyFile()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/emptyFile.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }

    public function testLogFileVariantsEmpty()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerEmptyLog.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( 'testTaskLogger', $logTask[ 'name' ] );
        $this->assertEquals( '/absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php', $logTask[ 'command' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:00:48', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );

        $this->assertEquals( 5, $logTask[ 'LogTaskParam' ]->count() );

        $this->assertEquals( 0, $logTask[ 'LogTaskMessage' ]->count() );
    }

    public function testLogFileVariantsError()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerErrorError.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( '2011-07-02 16:13:56', $logTask[ 'execution_start' ] );
        $this->assertEquals( '', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'error', $logTask[ 'status' ] );

        $this->assertEquals( 1, $logTask[ 'LogTaskMessage' ]->count() );

        $expectedErrorMessage = <<<EOF

Fatal error: triggered error in /absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php on line 43

EOF;

        $this->assertEquals( $expectedErrorMessage, $logTask[ 'LogTaskMessage' ][0]['message'] );
    }

    public function testLogFileVariantsNotice()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerErrorNotice.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( '2011-07-02 16:03:27', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:03:27', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'notice', $logTask[ 'status' ] );

        $this->assertEquals( 1, $logTask[ 'LogTaskMessage' ]->count() );

        $expectedErrorMessage = <<<EOF

Notice: triggered error in /absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php on line 37

EOF;

        $this->assertEquals( $expectedErrorMessage, $logTask[ 'LogTaskMessage' ][0]['message'] );
    }

    function testLogFileVariantsWarning()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerErrorWarning.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( '2011-07-02 16:04:16', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:04:16', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'warning', $logTask[ 'status' ] );

        $this->assertEquals( 1, $logTask[ 'LogTaskMessage' ]->count() );

        $expectedErrorMessage = <<<EOF

Warning: triggered error in /absolute/path/projectn/lib/task/mock/testTaskLoggerTask.class.php on line 40

EOF;

        $this->assertEquals( $expectedErrorMessage, $logTask[ 'LogTaskMessage' ][0]['message'] );
    }

    public function testLogFileVariantsSuccess()
    {
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerSuccess.log' );

        $logTask = Doctrine::getTable( 'LogTask' )->findOneById( 1 );

        $this->assertEquals( 1, Doctrine::getTable('LogTask')->count() );
        $this->assertEquals( '2011-07-02 16:01:54', $logTask[ 'execution_start' ] );
        $this->assertEquals( '2011-07-02 16:01:54', $logTask[ 'execution_end' ] );
        $this->assertEquals( 'success', $logTask[ 'status' ] );

        $this->assertEquals( 2, $logTask[ 'LogTaskMessage' ]->count() );

        $this->assertEquals( 'single line log message', $logTask[ 'LogTaskMessage' ][0]['message'] );

        $expectedErrorMessage = <<<EOF
 multi line
 log
  message

EOF;

        $this->assertEquals( $expectedErrorMessage, $logTask[ 'LogTaskMessage' ][1]['message'] );
    }

    public function testParseLogFileWithMultipleEntries()
    {
       $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerTaskCalls.log' );

       $this->assertEquals( 22, Doctrine::getTable('LogTask')->count() );

       //79 -> 70 messages = errors added outside of message block + 3 additional errors added by parser (in case of serious issue, like fatal error)
       $this->assertEquals( 79, Doctrine::getTable('LogTaskMessage')->count() );

       $this->assertEquals( 10, Doctrine::getTable('LogTaskParam')->count() );
    }

    public function testLogLineCount()
    {
       $testFile = TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerTaskCalls.log';
       $this->logFileParser->processFile( $testFile );
       $testFileContent = file_get_contents( $testFile );

       $logTasks =  Doctrine::getTable('LogTask')->findAll();

       $logLineCountInDb = 0;
       foreach( $logTasks as $logTask )
       {
            $logLineCountInDb++; //start line
            if ( !empty( $logTask[ 'command' ] ) ) $logLineCountInDb++; //file line
            if ( 0 < $logTask[ 'LogTaskParam' ]->count() ) $logLineCountInDb++; //file line
            foreach ( $logTask[ 'LogTaskMessage' ] as $message )
            {
                $logLineCountInDb += count( explode( PHP_EOL, $message['message'] ) );
            }
            if ( !empty( $logTask[ 'execution_end' ] ) ) $logLineCountInDb++; //file line
       }

       //274 lines in file + 3 lines added by the logger (extra notice on fatals)
       //+ 6 extra lines added after exception by the logger (if not in messages)
       $this->assertEquals( 282, $logLineCountInDb );
    }
    
    public function testNonParsableFile()
    {
        $this->setExpectedException( 'logFileParserException' );
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/nonParsableLogFile.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }

    public function testNoStartLineFound()
    {
        $this->setExpectedException( 'logFileParserException' );
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerNoStartLine.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }

    public function testTwoEndLinesAfterEachOther()
    {
        $this->setExpectedException( 'logFileParserException' );
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerTwoEndLineAfterEachOther.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }

    public function testMultipleCommandError()
    {
        $this->setExpectedException( 'logFileParserException' );
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerMultipleCommand.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }

    public function testMultipleParamsError()
    {
        $this->setExpectedException( 'logFileParserException' );
        $this->logFileParser->processFile( TO_TEST_DATA_PATH . '/log_parser/testTaskLoggerMultipleParameters.log' );
        $this->assertEquals( 0, Doctrine::getTable('LogTask')->count() );
    }
    
}