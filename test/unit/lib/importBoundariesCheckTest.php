<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';
/**
 * Test class for importBoundariesCheck
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class importBoundariesCheckTest extends PHPUnit_Framework_TestCase
{

    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        // add default vendors
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'ny' ) );       // vendor ID: 1
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'unknown' ) );  // vendor ID: 2
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'london' ) );   // vendor ID: 3
        ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'chicago' ) );  // vendor ID: 4
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    /**
     * In this test, we are testing the TASK this class is written FOR
     */
    public function testInexistingYmlFile()
    {
        $task = new importBoundariesCheckTask( new sfEventDispatcher, new sfFormatter );
        $this->setExpectedException( "ImportBoundariesCheckException" );
        
        $task->runFromCLI( new sfCommandManager, array('--yml=no_file') );
    }

    /**
     * When there os No Log Import records this error should be returned
     */
    public function testNoLogFoud()
    {
        // get the Errors
        $errors = $this->getErrors( );

        // vendor (unknown) should be exluded and it should add up to 3 log messages
        $this->assertEquals( 3, count( $errors ), 'there should be 3 errors returned as there is no Log found' );
    }

    /**
     * Query found record for Yesterday but No record found for Today!
     * This test to prove that importBoundaryCheck yield this error
     */
    public function testNoImportLogFoundForToday()
    {
        $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
        
        // Add LogImports for yesterday
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );   // Log Import ID: 1

        // get the Errors
        $errors = $this->getErrors( );

        // Error should be thors as there is no Log for TODAY!
        $this->assertContains( 'Error: No Import log found for date', $errors[0] );
    }

    /**
     * This is similar to testNoImportLogFoundToday() but no log found for Yesterdays date,
     * in order to check this, database should have todays log and above minimum boundary count
     * Yesterday records checked when importBoundaryCheck trying to calculate the dopped by percentage
     */
    public function testNoImportFoundForYesterday()
    {
        $today     = date('Y-m-d H:i:s' );

        // Add LogImports for today
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $today ) );       // Log Import ID: 1


        // add Logs to Make today voundaries are VALID for importboundaries to check for percentage DROP
        //Add LogImports for today
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'insert',
                                                               'count'         => 10 ) );
        // get the Errors
        $errors = $this->getErrors( );

        // since there is no yesterday LOG found, importoundaries can't calculate the dropped percentage...
        $this->assertContains( 'Error: No Import log found for date', $errors[0] );
        $this->assertStringEndsWith('to calculate drop percentage', $errors[0] );
    }

    /**
     * when an import completed, LogImport status should be changed to success.
     * othersie it means that Import did not complete properly and this test to see that
     * importBoundaryCheck throws error when it found anything but success
     */
    public function testImportFailedToComplete()
    {
        $today     = date('Y-m-d H:i:s' );

        // Add LogImports for today
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'status' => 'running',
                                                          'created_at' => $today ) );       // Log Import ID: 1
        // get the Errors
        $errors = $this->getErrors( );

        // Status of the Import is running, unless the status is success;
        // importBoundaryCheck should add error that this import did not complete
        $this->assertContains( 'failed to complete' , $errors[0] );
    }

    /**
     * Deviding Zero or By Zero will throw exception error, importBoundaryCheck will check for any records for
     * 0 total count value and return a graceful error message and not braking the process
     */
    public function testUnableToCalculatePercentageDevidedByZero()
    {
        $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
        $today     = date('Y-m-d H:i:s' );

        // Add LogImports for yesterday
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );   // Log Import ID: 1
        // Add LogImports for today
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $today ) );   // Log Import ID: 2

        //Add LogImport Count for yesterday
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 0) ); // Make it Zero to archive Devided By Zero
        //Add LogImport Count for today
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 2,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 10) ); // Make it valid, so it will pass through the Fell behind Error message

        // get the Errors
        $errors = $this->getErrors( );

        $this->assertContains( 'unable to calculate dropped percentage', $errors[0] );
    }

    /**
     * testing to see that importBoundaryCheck catched errors, when lower boundary not met or
     * dropped by more than given percentage.
     */
    public function testDroppedByPercentageAndFellBehindMinimum()
    {
        $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
        $today     = date('Y-m-d H:i:s' );

        // Add LogImports for yesterday
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1, // for Vendor 3 [ Ny ]
                                                          'created_at' => $yesterday ) );   // Log Import ID: 1

        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 3, // for Vendor 3 [ London ]
                                                          'created_at' => $yesterday ) );   // Log Import ID: 2
        // Add LogImports for today
        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1, // for Vendor 3 [ Ny ]
                                                          'created_at' => $today ) );   // Log Import ID: 3

        ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 3, // for Vendor 3 [ London ]
                                                          'created_at' => $today ) );   // Log Import ID: 4

        //Add LogImport Count for yesterday
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 7) ); // 7 should fell behind the minimum boundary
        
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 2,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 5) ); // 5 and when today's cound drop more than 2% it should throw error
        //Add LogImport Count for today
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 3,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 7) ); // 7 should fell behind the minimum boundary

        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 4,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'insert',
                                                               'count'         => 1) );
        
        ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 4,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 1) ); // insert + failed = 2 that is (100 - ( ( 2 / 5 ) * 100 ) ) = 60% drop

        // get the Errors
        $errors = $this->getErrors( );

        // First one is NY which will throw fell behind minimum error and
        // two others; 2nd and 3rd will be NY's event and Movie which we are not checking!
        $this->assertContains( 'fell behind the minimum', $errors[0] );

        // 4th error message would be the London error saying that Poi is dropped by 60 %
        $this->assertContains( 'dropped by 60%', $errors[3] );

        // in total there should be 7 Error messages as last one (chicago) don't have any import log so it will throw only one Error
        $this->assertEquals( 7 , count($errors) );
        
    }

    /**
     * generate Temporary Yaml File and return Filepath
     * @return string
     */
    private function generateYamlAndReturnPath()
    {
        $yml = array(
            'default' => array( 
                'poi' => array(
                    'minimum' => 2,
                    'threshold' => 1,
                ), 
                'event' => array(
                    'minimum' => 2,
                    'threshold' => 1,
                ),
                'movie' => array(
                    'minimum' => 2,
                    'threshold' => 1,
                )
            ),
            'ny' => array(
                'poi' => array( 'minimum' => 8, 'eventoccurrence' => 2 )
            ),
            'exclude' => array(
                'unknown',
            )
        );

        $ymlData = sfYaml::dump( $yml );

        $fileName = tempnam( '/tmp', 'yml' );
        
        file_put_contents( $fileName , $ymlData );

        return $fileName;
    }

    /**
     * Create the instance of importBoundariesCheck and return Errors
     * @return array
     */
    private function getErrors( )
    {
        // generate YAML file
        $ymlFilename = $this->generateYamlAndReturnPath();

        // have to clear OLD errors
        $importCheck = new importBoundariesCheck( array( 'yml' => $ymlFilename ) );
        return $importCheck->getErrors();

        unset( $importCheck ) ;
        // End
        unlink( $ymlFilename ); // Remove the TMP file
    }
}

?>
