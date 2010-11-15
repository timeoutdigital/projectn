<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogImportTable Model
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
class LogImportTableTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $vendor['city'] = 'vendor a';
    $vendor->save();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $vendor['city'] = 'vendor b';
    $vendor->save();

    $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
    $vendor['city'] = 'vendor c';
    $vendor->save();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetAllByCityName()
  {
      $vendors = Doctrine::getTable( 'Vendor' )->findAll();

      $this->assertEquals( 3, $vendors->count() );
      $this->assertEquals( 'vendor a', $vendors[0]['city'] );
      $this->assertEquals( 'vendor b', $vendors[1]['city'] );
      $this->assertEquals( 'vendor c', $vendors[2]['city'] );

      // Add Log Data
      $logImport = ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id' => 2 ) );

      $vendorByCity = Doctrine::getTable( 'LogImport' )->getAllByCityName( 'vendor b' );

      $this->assertEquals( 1, $vendorByCity->count() );
      
      $this->assertEquals( 'vendor b', $vendorByCity[0]['Vendor']['city'] );
      
  }

  public function testGetLogImportCount()
  {
      $vendors = Doctrine::getTable( 'Vendor' )->findAll();
      $this->assertEquals( 3, $vendors->count() );
      
      $vendor1 = Doctrine::getTable( 'Vendor' )->findOneById(1);
      $vendor2 = Doctrine::getTable( 'Vendor' )->findOneById(2);
      $vendor3 = Doctrine::getTable( 'Vendor' )->findOneById(3);
      
      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      // Add LogImports for yesterday
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );

      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 2,
                                                          'created_at' => $yesterday ) );
      
      $this->assertEquals(2, Doctrine::getTable( 'LogImport' )->count() );
      
      //Add LogImports for today
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id' => 1,

                                                          'created_at' => $today ) );
      
      $this->assertEquals(3, Doctrine::getTable( 'LogImport' )->count() );

      // add LogImportCounts
      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1, 
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 5 ) );

      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1, 
                                                               'model'         => 'Event',
                                                               'operation'     => 'insert', 
                                                               'count'         => 100 ) );
      
      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 3, 
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 275  ) );
      
      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 2, 
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 275  ) );
      
      $this->assertEquals(4, Doctrine::getTable( 'LogImportCount' )->count() );

      //today
      $importLogsWithCountsForVendor3Today = Doctrine::getTable( 'LogImport' )
                                             ->getLogImportWithCountRecords( $vendor3[ 'id' ], $today, $today );

      $this->assertEquals( 0, $importLogsWithCountsForVendor3Today->count(),
                           'There should be no results for vendor 3 because it has no log import counts' );
      
      $importLogsWithCountsForVendor1Today = Doctrine::getTable( 'LogImport' )
                                             ->getLogImportWithCountRecords( $vendor1[ 'id' ], $today, $today );
      
      $this->assertEquals( 1, $importLogsWithCountsForVendor1Today->count(),
                           'There should be one logImport record for vendor1 today' );

      $this->assertEquals( 1, $importLogsWithCountsForVendor1Today[0]['LogImportCount']->count(),
                           'There should be one import count for vendor1 today' );

      //today and yesterday
      $importLogsWithCountsForVendor1Today = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $vendor1[ 'id' ], $yesterday, $today );
      $this->assertEquals( 2, $importLogsWithCountsForVendor1Today->count(),
                           'There should be one logImport record for vendor1 today' );

      $this->assertEquals( 2, $importLogsWithCountsForVendor1Today[0]['LogImportCount']->count(), 
                           'There should be two import count for vendor1 yesterday' );

      $this->assertEquals( 1, $importLogsWithCountsForVendor1Today[1]['LogImportCount']->count(),
                           'There should be one import count for vendor1 today' );

      // vendor2 today and yesterday
      $importLogsWithCountsForVendor2TodayYesterday = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $vendor2[ 'id' ], $yesterday, $today );
      $this->assertEquals( 1, $importLogsWithCountsForVendor2TodayYesterday->count(),
                           'There should be one logImport record for vendor2 yesterday and today' );

      $this->assertEquals( 1, $importLogsWithCountsForVendor2TodayYesterday[0]['LogImportCount']->count(),
                           'There should be onne import count for vendor2 yesterday' );
  }

  public function testGetLogImportWithCountRecordsByDates()
  {
      $vendors = Doctrine::getTable( 'Vendor' )->findAll();
      $this->assertEquals( 3, $vendors->count() );

      $vendor1 = Doctrine::getTable( 'Vendor' )->findOneById(1);
      $vendor2 = Doctrine::getTable( 'Vendor' )->findOneById(2);
      $vendor3 = Doctrine::getTable( 'Vendor' )->findOneById(3);

      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      // Add LogImports for yesterday
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );

      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 2,
                                                          'created_at' => $yesterday ) );

      //Add LogImports for today
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id' => 3,

                                                          'created_at' => $today ) );

      $this->assertEquals(3, Doctrine::getTable( 'LogImport' )->count() );

      // add LogImportCounts
      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 5 ) );

      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 2,
                                                               'model'         => 'Event',
                                                               'operation'     => 'insert',
                                                               'count'         => 100 ) );

      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 3,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 275  ) );
      //today
      $importLogsWithCountsForToday = Doctrine::getTable( 'LogImport' )
                                             ->getLogImportWithCountRecordsByDates( $today, $today );

      $this->assertEquals( 1, $importLogsWithCountsForToday->count(),
                           'There should be 1 results for Today' );
      // Today + yesterday
      $importLogsWithCountsForTodayYesterday = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByDates($yesterday, $today );

      $this->assertEquals( 3, $importLogsWithCountsForTodayYesterday->count(),
                           'There is total of 3 Logs exists for Today & yesterday' );
  }

  public function testGetLogImportWithCountRecordsByModelAndStatus()
  {
      $vendors = Doctrine::getTable( 'Vendor' )->findAll();
      $this->assertEquals( 3, $vendors->count() );

      $vendor1 = Doctrine::getTable( 'Vendor' )->findOneById(1);
      $vendor2 = Doctrine::getTable( 'Vendor' )->findOneById(2);
      $vendor3 = Doctrine::getTable( 'Vendor' )->findOneById(3);

      $yesterday = date('Y-m-d H:i:s' , strtotime( '-1 day' ) );
      $today     = date('Y-m-d H:i:s' );
      $tomorrow  = date('Y-m-d H:i:s' , strtotime( '+1 day' )  );

      //Add LogImports for today
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id' => 1,
                                                          'created_at' => $today ) );

      // add LogImportCounts
      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'failed',
                                                               'count'         => 5 ) );

      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Event',
                                                               'operation'     => 'insert',
                                                               'count'         => 100 ) );

      ProjectN_Test_Unit_Factory::add( 'LogImportCount', array('log_import_id' => 1,
                                                               'model'         => 'Poi',
                                                               'operation'     => 'existing',
                                                               'count'         => 275  ) );
      $this->assertEquals( 3, Doctrine::getTable( 'LogImportCount' )->findAll()->count() );

      // Assert
      $logImportWithCountForPoi = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByModelAndStatus( 'Poi', null, $yesterday, $today );
      $this->assertEquals( 1, $logImportWithCountForPoi->count(), 'Thete should be 1 Log Import ');
      $this->assertEquals( 2, $logImportWithCountForPoi[0]['LogImportCount']->count(), 'There are 2 POI log found in Database');

      $logImportWithCountForPoi = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByModelAndStatus( 'Poi', 'failed', $today, $today );
      $this->assertEquals( 1, $logImportWithCountForPoi[0]['LogImportCount']->count(), 'There si only 1 POI with status of failed');
  }

}