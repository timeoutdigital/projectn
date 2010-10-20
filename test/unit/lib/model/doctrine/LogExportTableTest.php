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

       $this->assertEquals(6, Doctrine::getTable( 'LogExportCount' )->count() );

       // today check
       $exportLogWithCountForVendor1Today = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( $vendor1['id'], 'Poi' );
       $this->assertEquals( 0, $exportLogWithCountForVendor1Today->count(), 'should be 0, vendor 1 have no export log for today' );

       $exportLogWithCountForVendor2TodayPoi = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( $vendor2['id'], 'Poi' );
       $this->assertEquals( 1, $exportLogWithCountForVendor2TodayPoi->count(), 'should be 1 count, vendor 1 have Poi exported today' );

       $exportLogWithCountForVendor2TodayEvent = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( $vendor2['id'], 'Event' );
       $this->assertEquals( 1, $exportLogWithCountForVendor2TodayEvent->count(), 'should be 1 count, vendor 1 have Event exported today' );

       $exportLogWithCountForVendor2TodayMovie = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( $vendor2['id'], 'Movie' );
       $this->assertEquals( 0, $exportLogWithCountForVendor2TodayMovie->count(), 'should be 0 count, vendor 1 don\'t have any Movies exported Today' );

       // test Hydrated Array
       $hydratedArrayResults = Doctrine::getTable( 'LogExport' )->getTodaysLogExportWithCountRecords( $vendor2['id'], 'Event', Doctrine_Core::HYDRATE_ARRAY );
       $this->assertTrue( is_array( $hydratedArrayResults ) );
  }
  
}