<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class processLogFilesTaskTest extends PHPUnit_Framework_TestCase
{

    protected $relativTestDataPath = '../test/unit/data/log_parser';



    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        $this->task = new processLogfiles( new sfEventDispatcher, new sfFormatter );
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();

        $path = sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath;

        $folders = DirectoryIteratorN::iterate( $path, DirectoryIteratorN::DIR_FOLDERS );

        $matches = array();
        if ( isset( $folders[ 0 ] ) && preg_match('/_parsed_[0-9]{4}\-[0-9]{2}\-[0-9]{2}_[0-9]{2}\-[0-9]{2}\-[0-9]{2}\-[0-9]{2}/', $folders[ 0 ], $matches ) === 1 )
        {
            $files = DirectoryIteratorN::iterate( $path . '/' . $folders[ 0 ], DirectoryIteratorN::DIR_FILES );

            foreach( $files as $file )
            {
                rename( $path . '/' . $folders[ 0 ]. '/' . $file, $path . '/' . $file);
            }

            rmdir($path . '/' . $folders[ 0 ]);
        }
    }


    public function testFileRotation()
    {
        //make sure that our test data is fine before the task
        $files = DirectoryIteratorN::iterate( sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath, DirectoryIteratorN::DIR_FILES );
        $folders = DirectoryIteratorN::iterate( sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath, DirectoryIteratorN::DIR_FOLDERS );

        $this->assertEquals( 14, count( $files ) );
        $this->assertEquals( 'emptyFile.log', $files[ 0 ] );
        $this->assertEquals( 'nonParsableLogFile.log', $files[ 1 ] );
        $this->assertEquals( 'testTaskLoggerEmptyLog.log', $files[ 2 ] );
        $this->assertEquals( 'testTaskLoggerEmptyNoFilesNoParamLog.log', $files[ 3 ] );
        $this->assertEquals( 'testTaskLoggerErrorError.log', $files[ 4 ] );
        $this->assertEquals( 'testTaskLoggerErrorNotice.log', $files[ 5 ] );
        $this->assertEquals( 'testTaskLoggerErrorWarning.log', $files[ 6 ] );
        $this->assertEquals( 'testTaskLoggerMulitpleVendors.log', $files[ 7 ] );
        $this->assertEquals( 'testTaskLoggerMultipleCommand.log', $files[ 8 ] );
        $this->assertEquals( 'testTaskLoggerMultipleParameters.log', $files[ 9 ] );
        $this->assertEquals( 'testTaskLoggerNoStartLine.log', $files[ 10 ] );
        $this->assertEquals( 'testTaskLoggerSuccess.log', $files[ 11 ] );
        $this->assertEquals( 'testTaskLoggerTaskCalls.log', $files[ 12 ] );
        $this->assertEquals( 'testTaskLoggerTwoEndLineAfterEachOther.log', $files[ 13 ] );

        $this->assertEquals( 1, count( $folders ) );
        $this->assertEquals( 'some_folder_which_should_not_be_affected', $folders[ 0 ] );

        echo $this->_runTask();

        //check the structure after the task
        $files = DirectoryIteratorN::iterate( sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath, DirectoryIteratorN::DIR_FILES );
        $folders = DirectoryIteratorN::iterate( sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath, DirectoryIteratorN::DIR_FOLDERS );

        $this->assertEquals( 5, count( $files ) );
        $this->assertEquals( 'nonParsableLogFile.log', $files[ 0 ] );

        $this->assertEquals( 2, count( $folders ) );
        $this->assertRegExp( '/_parsed_[0-9]{4}\-[0-9]{2}\-[0-9]{2}_[0-9]{2}\-[0-9]{2}\-[0-9]{2}\-[0-9]{2}/', $folders[ 0 ] );
        $this->assertEquals( 'some_folder_which_should_not_be_affected', $folders[ 1 ] );

        $parsedFiles = DirectoryIteratorN::iterate( sfConfig::get( 'sf_log_dir' ) . '/' . $this->relativTestDataPath . '/' . $folders[ 0 ], DirectoryIteratorN::DIR_FILES );

        $this->assertEquals( 9, count( $parsedFiles ) );                                        //task, param, message
        $this->assertEquals( 'emptyFile.log', $parsedFiles[ 0 ] );                              //0,0,0
        $this->assertEquals( 'testTaskLoggerEmptyLog.log', $parsedFiles[ 1 ] );                 //1,5,0
        $this->assertEquals( 'testTaskLoggerEmptyNoFilesNoParamLog.log', $parsedFiles[ 2 ] );   //1,0,0
        $this->assertEquals( 'testTaskLoggerErrorError.log', $parsedFiles[ 3 ] );               //1,+1,1
        $this->assertEquals( 'testTaskLoggerErrorNotice.log', $parsedFiles[ 4 ] );              //1,0,1
        $this->assertEquals( 'testTaskLoggerErrorWarning.log', $parsedFiles[ 5 ] );             //1,0,1
        $this->assertEquals( 'testTaskLoggerMulitpleVendors.log', $parsedFiles[ 6 ] );          //4,2,8
        $this->assertEquals( 'testTaskLoggerSuccess.log', $parsedFiles[ 7 ] );                  //1,0,2
        $this->assertEquals( 'testTaskLoggerTaskCalls.log', $parsedFiles[ 8 ] );                //22,+5,79
    }

    /**
     * this test is very important, it also test that the transactions work fine!
     */
    public function testStuffAddedToDb()
    {
       echo $this->_runTask();

       $this->assertEquals( 32, Doctrine::getTable('LogTask')->count() );

       //80 messages, 9 errors outside of a message, 5 items added by logger
       $this->assertEquals( 94, Doctrine::getTable('LogTaskMessage')->count() );

       $this->assertEquals( 16, Doctrine::getTable('LogTaskParam')->count() );
    }


    private function _runTask()
    {
        ob_start();
        $this->task->runFromCLI( new sfCommandManager, '--log-dirs="' . $this->relativTestDataPath . '"' );
        return ob_get_clean();
    }
}
