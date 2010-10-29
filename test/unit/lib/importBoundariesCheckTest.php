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

    public function testGetPrcentageDiffByXDaysForAVendor()
    {
        $days = 1; // Number of days to compare the Average! 1 == 2 ( $days * 2 )
        $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
        $today     = date('Y-m-d H:i:s' );

        // generate YAML file
        $ymlFilename = $this->generateYamlAndReturnPath();

        // Add LogImports for yesterday
        $this->addLogImport( 1, $yesterday );

        // Add LogImports for today
        $this->addLogImport( 1, $today );

        //Add LogImport Count for yesterday
        $this->addLogImportCount( 1, "Poi", 20 );
        $this->addLogImportCount( 1, "Event", 15 );
        $this->addLogImportCount( 1, "Movie", 20 );

        //Add LogImport Count for today
        $this->addLogImportCount( 2, "Poi", 10 );
        $this->addLogImportCount( 2, "Event", 15 );
        $this->addLogImportCount( 2, "Movie", 21 );
        
        // have to clear OLD errors
        $importCheck = new importBoundariesCheck( array( 'yml' => $ymlFilename ) );

        $changes = $importCheck->getPrcentageDiffByXDays( $days, 1 );

        $this->assertTrue( is_array($changes ), 'return should be an Array' );
        $this->assertEquals( 1, count( $changes), 'Since we send Vendor, it should only return One Set of result' );
        $keys = array_keys( $changes );
        $this->assertEquals( 'ny', $keys[0], 'Key NY should be in Array');
        $this->assertEquals( 3, count( $changes['ny'] ), 'NY should have 3 Models calculations' );
        $this->assertEquals( -50, $changes['ny']['poi'], 'NY:Poi should show 50% Drop (-50)' );
        $this->assertEquals( 0, $changes['ny']['event'], 'NY:Event should show 0% change' );
        $this->assertEquals( 5, $changes['ny']['movie'], 'NY:Movie should show 5% Increase (5)' );

        unlink( $ymlFilename );
        
    }
    
    public function testGetPrcentageDiffBy7DaysForAVendor()
    {
        $days = 7; // Number of days to compare the Average! 7 == 14 ( $days * 2 )
        $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
        $today     = date('Y-m-d H:i:s' );

        // generate YAML file
        $ymlFilename = $this->generateYamlAndReturnPath();

        // Add LogImports
        for( $i = ( $days  * 2 ); $i >= 0 ; $i-- )
        {
            if( $i == 0)
            {
                $logImport = $this->addLogImport( 1, date('Y-m-d H:i:s' ) );
            }else
            {
                $logImport = $this->addLogImport( 1, date('Y-m-d H:i:s' , strtotime( "-{$i} day" ) ) );
            }
            $logCount = $this->addLogImportCount( $logImport->id, "Poi", 10 );
            $logCount = $this->addLogImportCount( $logImport->id, "Event", 10 );
            $logCount = $this->addLogImportCount( $logImport->id, "Movie", 10 );
        }

        $this->assertEquals( 15, Doctrine::getTable( 'LogImport' )->findAll()->count() );
        $this->assertEquals( ( 15 * 3 ), Doctrine::getTable( 'LogImportCount' )->findAll()->count() );
        
        // Set some figures for testing
        $this->addLogImportCount( 1, "Poi", 5, 'failed');
        $this->addLogImportCount( 1, "Movie", 3, 'failed');
        $this->addLogImportCount( 4, "Movie", 1, 'failed');
        $this->addLogImportCount( 4, "Poi", 6, 'failed');
        $this->addLogImportCount( 7, "Poi", 6, 'failed');
        $this->addLogImportCount( 7, "Event", 8, 'failed');
        $this->addLogImportCount( 8, "Event", 5, 'failed');
        $this->addLogImportCount( 9, "Poi", 5, 'failed');
        $this->addLogImportCount( 10, "Poi", 2, 'failed');
        $this->addLogImportCount( 13, "Movie", 2, 'failed');
        $this->addLogImportCount( 14, "Event", 8, 'failed');
        $this->addLogImportCount( 1, "EventOccurrence", 1, 'failed');
        $this->addLogImportCount( 14, "EventOccurrence", 1, 'failed');
        $this->addLogImportCount( 15, "EventOccurrence", 1, 'failed');
        
        // Get the Percentage changes
        $importCheck = new importBoundariesCheck( array( 'yml' => $ymlFilename ) );

        $changes = $importCheck->getPrcentageDiffByXDays( $days );

        $this->assertTrue( is_array($changes ), 'return should be an Array' );
        $this->assertEquals( 1, count( $changes), 'There should be 1 vendor, as other should not be included since they dont have any Logs' );
        $keys = array_keys( $changes );
        $this->assertEquals( 'ny', $keys[0], 'Key NY should be in Array');
        $this->assertEquals( 4, count( $changes['ny'] ), 'NY should have 4 Models calculations, it has EventOccurrence' );

        // Check calculations, it returned in float values
        $this->assertEquals( "-11.4943", round($changes['ny']['poi'], 4), 'NY:Poi should show 15% Drop (-15)' );
        $this->assertEquals( "6.4103", round($changes['ny']['event'], 4), 'NY:Event should show 0% Change' );
        $this->assertEquals( "-2.7027", round($changes['ny']['movie'], 4), 'NY:Movie should show 0% Change' );
        $this->assertEquals( 0, $changes['ny']['eventoccurrence'], 'NY:EventOccurrence should show 0 change' );

        unlink( $ymlFilename );

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
        $this->addLogImport( 1, $yesterday ); // for Vendor [ Ny ]

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
        $this->addLogImport( 1, $today ); // for Vendor [ Ny ]

        // add Logs to Make today voundaries are VALID for importboundaries to check for percentage DROP
        //Add LogImports for today
        $this->addLogImportCount( 1, 'Poi', 10 , 'insert'); // Make it Zero to archive Devided By Zero
        
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
        $this->addLogImport( 1, $yesterday ); // for Vendor [ Ny ]
        
        // Add LogImports for today
        $this->addLogImport( 1, $today ); // for Vendor [ Ny ]
        

        //Add LogImport Count for yesterday
        $this->addLogImportCount( 1, 'Poi', 0 , 'failed'); // Make it Zero to archive Devided By Zero
        $this->addLogImportCount( 2, 'Poi', 10 , 'existing'); // Make it valid, so it will pass through the Fell behind Error message

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
        
        $this->addLogImport( 1, $yesterday ); // for Vendor 3 [ Ny ]
        $this->addLogImport( 3, $yesterday ); // for Vendor 3 [ London ]

        // Add LogImports for today
        $this->addLogImport( 1, $today ); // for Vendor 3 [ Ny ]
        $this->addLogImport( 3, $today ); // for Vendor 3 [ London ]
        

        //Add LogImport Count for yesterday
        $this->addLogImportCount( 1, 'Poi', 7 , 'existing'); // 7 should fell behind the minimum boundary
        $this->addLogImportCount( 2, 'Poi', 5 , 'existing'); // 5 and when today's cound drop more than 2% it should throw error
        $this->addLogImportCount( 3, 'Poi', 7 , 'failed'); // 7 should fell behind the minimum boundary
        $this->addLogImportCount( 4, 'Poi', 1 , 'insert'); // insert + failed = 2 that is (100 - ( ( 2 / 5 ) * 100 ) ) = 60% drop
        $this->addLogImportCount( 4, 'Poi', 1 , 'failed'); // insert + failed = 2 that is (100 - ( ( 2 / 5 ) * 100 ) ) = 60% drop

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
        $importCheck->processImportLog();
        unlink( $ymlFilename ); // Remove the TMP file

        return $importCheck->getErrors();        
    }

    private function addLogImport( $vendorID, $date )
    {
        $logImport = ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => $vendorID,
                                                          'created_at' => $date ) );   // Log Import ID: 1
        return $logImport;
    }
    
    private function addLogImportCount( $logImportID, $model, $count = 10, $status = 'existing' )
    {
        $count = ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => $logImportID,
                                                               'model'         => ucfirst( $model ),
                                                               'operation'     => $status,
                                                               'count'         => $count) );
        return $count;
    }
}

?>
