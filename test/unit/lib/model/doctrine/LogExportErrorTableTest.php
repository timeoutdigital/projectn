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
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class LogExportErrorTableTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetLogExportErrors()
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
      
      // add log export for yesterday
      ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );

      ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id'  => 2,
                                                          'created_at' => $yesterday ) );

      $this->assertEquals(2, Doctrine::getTable( 'LogExport' )->count() );

      //Add LogExports for today
      ProjectN_Test_Unit_Factory::add( 'LogExport', array('vendor_id' => 1,
                                                          'created_at' => $today ) );

      $this->assertEquals(3, Doctrine::getTable( 'LogExport' )->count() );

      // add LogExportError for Yesterday
      ProjectN_Test_Unit_Factory::add( 'LogExportError', array('log_export_id' => 1,
                                                               'model' => 'Poi',
                                                               'log' => 'poi 1 broken',
                                                               'created_at' => $yesterday ) );
      
      ProjectN_Test_Unit_Factory::add( 'LogExportError', array('log_export_id' => 1,
                                                               'model' => 'Poi',
                                                               'log' => 'poi 2 broken',
                                                               'created_at' => $yesterday ) );
      
      ProjectN_Test_Unit_Factory::add( 'LogExportError', array('log_export_id' => 2,
                                                               'model' => 'Event',
                                                               'log' => 'Event 1 broken',
                                                               'created_at' => $yesterday ) );

      // add LogExportError for Today
      ProjectN_Test_Unit_Factory::add( 'LogExportError', array('log_export_id' => 3,
                                                               'model' => 'Poi',
                                                               'log' => 'poi 1 broken again',
                                                               'created_at' => $today ) );

      ProjectN_Test_Unit_Factory::add( 'LogExportError', array('log_export_id' => 3,
                                                               'model' => 'Event',
                                                               'log' => 'new poi 1 broken for 2',
                                                               'created_at' => $today ) );

      $this->assertEquals( 5, Doctrine::getTable( 'LogExportError' )->findAll()->count(), 'There should be 5 LogExportErrors in Database' );

      // today
      $exportErrorsForVendor1TodayPoi = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor1[ 'id' ], 'Poi', $today, $today );
      $this->assertEquals( 1, $exportErrorsForVendor1TodayPoi->count(), 'There should be 1 LogExportError for vendor1 poi' );

      $exportErrorsForVendor1TodayEvent = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor1[ 'id' ], 'Event', $today, $today );
      $this->assertEquals( 1, $exportErrorsForVendor1TodayEvent->count(), 'There should be 1 LogExportError for vendor1 Event' );

      $exportErrorsForVendor1TodayMovie = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor1[ 'id' ], 'Movie', $today, $today );
      $this->assertEquals( 0, $exportErrorsForVendor1TodayMovie->count(), 'There should be no LogExportError for vendor1 Movie' );

      $exportErrorsForVendor2TodayPoi = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor2[ 'id' ], 'Poi', $today, $today );
      $this->assertEquals( 0, $exportErrorsForVendor2TodayPoi->count(), 'There should be no LogExportError for vendor2 poi today' );
      
      // today and yesterday
      $exportErrorsForVendor1TodayPoi = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor1[ 'id' ], 'Poi', $yesterday, $today );
      $this->assertEquals( 3, $exportErrorsForVendor1TodayPoi->count(), 'There should be 2 LogExportError for vendor1 poi today and yesterday' );

      $exportErrorsForVendor2TodayEvent = Doctrine::getTable( 'LogExportError' )
                                                ->getLogExportErrors( $vendor2[ 'id' ], 'Event', $yesterday, $today );
      $this->assertEquals( 1, $exportErrorsForVendor2TodayEvent->count(), 'There should be 1 LogExportError for vendor2 event today and yesterday' );

      
  }

}