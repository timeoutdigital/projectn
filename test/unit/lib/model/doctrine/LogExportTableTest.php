<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogExportTable Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class LogExportTableTest extends PHPUnit_Framework_TestCase
{
    /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetTodaysLogExportWithCountRecords()
  {
      $this->populateDatabaseWithData(); // Dummy Data
      
       $this->assertEquals(6, Doctrine::getTable( 'LogExportCount' )->count() );

       // today check
       $exportLogWithCountForVendor1Today = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( 1, 'Poi' );
       $this->assertEquals( 0, $exportLogWithCountForVendor1Today->count(), 'should be 0, vendor 1 have no export log for today' );

       $exportLogWithCountForVendor2TodayPoi = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( 2, 'Poi' );
       $this->assertEquals( 1, $exportLogWithCountForVendor2TodayPoi->count(), 'should be 1 count, vendor 1 have Poi exported today' );

       $exportLogWithCountForVendor2TodayEvent = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords(2, 'Event' );
       $this->assertEquals( 1, $exportLogWithCountForVendor2TodayEvent->count(), 'should be 1 count, vendor 1 have Event exported today' );

       $exportLogWithCountForVendor2TodayMovie = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( 2, 'Movie' );
       $this->assertEquals( 0, $exportLogWithCountForVendor2TodayMovie->count(), 'should be 0 count, vendor 1 don\'t have any Movies exported Today' );

       // test Hydrated Array
       $hydratedArrayResults = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( 2, 'Event', Doctrine_Core::HYDRATE_ARRAY );
       $this->assertTrue( is_array( $hydratedArrayResults ) );
  }

  public function testGetLogExportWithCountRecords()
  {
      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      $this->populateDatabaseWithData(); // Dummy Data

      // Today
      $exportLogsWithCountsForVendor2Today = Doctrine::getTable( 'LogExport' )
                                             ->getLogExportWithCountRecords( 2, $today, $today, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals( 1, count( $exportLogsWithCountsForVendor2Today ),
                           'There should be 1record for vendor 2 today' );
      
      $this->assertEquals( 2, count( $exportLogsWithCountsForVendor2Today[0]['LogExportCount'] ),
                           'There should be 2 log export count record for vendor 2 today' );

      $exportLogsWithCountsForVendor1Today = Doctrine::getTable( 'LogExport' )
                                             ->getLogExportWithCountRecords( 1, $today, $today, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals( 0, count($exportLogsWithCountsForVendor1Today),
                           'There should be 0 record for vendor 1 today' );

      // yesterday
      $exportLogsWithCountsForVendor1Yesterday = Doctrine::getTable( 'LogExport' )
                                             ->getLogExportWithCountRecords( 1, $yesterday, $yesterday, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals( 1, count($exportLogsWithCountsForVendor1Yesterday),
                           'There should be 1 record for vendor 1 yesterday' );
      
      $this->assertEquals( 2, count($exportLogsWithCountsForVendor1Yesterday[0]['LogExportCount']),
                           'There should be 2 log count record for vendor 1 yesterday' );

      // vendor2 today and yesterday
      $exportLogsWithCountsForVendor2TodayYesterday = Doctrine::getTable( 'LogExport' )
                                             ->getLogExportWithCountRecords( 2, $yesterday, $today, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals( 2, count( $exportLogsWithCountsForVendor2TodayYesterday ),
                           'There should be 2 record for vendor 2 yesterday + today' );
      $this->assertEquals( 4, count( $exportLogsWithCountsForVendor2TodayYesterday[0]['LogExportCount'] ) + count( $exportLogsWithCountsForVendor2TodayYesterday[1]['LogExportCount'] ) ,
                           'There should be 4 LogExportCount record for vendor 2 yesterday + today' );
  }

  public function testGetLogExportWithCountRecordsByDates()
  {
      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      $this->populateDatabaseWithData(); // Dummy Data

      // Today
      $exportLogsWithCountsByDatesToday =  Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecordsByDates( $today, $today, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals(1 , count( $exportLogsWithCountsByDatesToday ), 'There is only 1 Log for Today');

      // yesterday
      $exportLogsWithCountsByDatesYesterday =  Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecordsByDates( $yesterday, $yesterday, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals(2 , count( $exportLogsWithCountsByDatesYesterday ), 'There are 2 Log for Yesterday');

      // today + yesterday
      $exportLogsWithCountsByDatesTodayAndYesterday =  Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecordsByDates( $yesterday, $today, Doctrine_Core::HYDRATE_ARRAY );
      $this->assertEquals(3 , count( $exportLogsWithCountsByDatesTodayAndYesterday ), 'There are 3 Log for Today + Yesterday');
  }

  
  private function populateDatabaseWithData()
  {
      // add vendors
      $vendor1 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $vendor1['city'] = 'vendor a';
      $vendor1->save();

      $vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
      $vendor2['city'] = 'vendor b';
      $vendor2->save();

      $this->assertEquals( 2, Doctrine::getTable( 'Vendor' )->findAll()->count() );

      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      // add LogExport for yesterday
       ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );
       ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id'  => 2,
                                                          'created_at' => $yesterday ) );

       // ass LogExport for Today
       ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id'  => 2,
                                                          'created_at' => $today ) );

       $this->assertEquals(3, Doctrine::getTable( 'LogExport' )->count() );

       // add LogExportCounts
       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 1,
                                                               'model'         => 'Poi',
                                                               'count'         => 78 ) );

       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 1,
                                                               'model'         => 'Movie',
                                                               'count'         => 978 ) );

       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 2,
                                                               'model'         => 'Poi',
                                                               'count'         => 28 ) );

       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 2,
                                                               'model'         => 'Event',
                                                               'count'         => 45 ) );


       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 3,
                                                               'model'         => 'Poi',
                                                               'count'         => 35 ) );

       ProjectN_Test_Unit_Factory::add( 'LogExportCount', array('log_export_id' => 3,
                                                               'model'         => 'Event',
                                                               'count'         => 10 ) );
  }
}