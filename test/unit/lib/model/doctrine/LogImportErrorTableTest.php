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
class LogImportErrorTableTest extends PHPUnit_Framework_TestCase
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

  public function testGetLogImportErrors()
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
      
      // add log import for yesterday
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 1,
                                                          'created_at' => $yesterday ) );

      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id'  => 2,
                                                          'created_at' => $yesterday ) );

      $this->assertEquals(2, Doctrine::getTable( 'LogImport' )->count() );

      //Add LogImports for today
      ProjectN_Test_Unit_Factory::add( 'LogImport', array('vendor_id' => 1,
                                                          'created_at' => $today ) );

      $this->assertEquals(3, Doctrine::getTable( 'LogImport' )->count() );

      // add LogImportError for Yesterday
      ProjectN_Test_Unit_Factory::add( 'LogImportError', array('log_import_id' => 1,
                                                               'model' => 'Poi',
                                                               'message' => 'poi 1 broken',
                                                               'created_at' => $yesterday ) );
      
      ProjectN_Test_Unit_Factory::add( 'LogImportError', array('log_import_id' => 1,
                                                               'model' => 'Poi',
                                                               'message' => 'poi 2 broken',
                                                               'created_at' => $yesterday ) );
      ProjectN_Test_Unit_Factory::add( 'LogImportError', array('log_import_id' => 2,
                                                               'model' => 'Event',
                                                               'message' => 'Event 1 broken',
                                                               'created_at' => $yesterday ) );

      // add LogImportError for Today
      ProjectN_Test_Unit_Factory::add( 'LogImportError', array('log_import_id' => 3,
                                                               'model' => 'Poi',
                                                               'message' => 'poi 1 broken again',
                                                               'created_at' => $today ) );
      ProjectN_Test_Unit_Factory::add( 'LogImportError', array('log_import_id' => 3,
                                                               'model' => 'Event',
                                                               'message' => 'new poi 1 broken for 2',
                                                               'created_at' => $today ) );

      $this->assertEquals( 5, Doctrine::getTable( 'LogImportError' )->findAll()->count(), 'There should be 5 LogImportErrors in Database' );

      // today
      $importErrorsForVendor1TodayPoi = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor1[ 'id' ], 'Poi', $today, $today );
      $this->assertEquals( 1, $importErrorsForVendor1TodayPoi->count(), 'There should be 1 LogImportError for vendor1 poi' );

      $importErrorsForVendor1TodayEvent = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor1[ 'id' ], 'Event', $today, $today );
      $this->assertEquals( 1, $importErrorsForVendor1TodayEvent->count(), 'There should be 1 LogImportError for vendor1 Event' );

      $importErrorsForVendor1TodayMovie = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor1[ 'id' ], 'Movie', $today, $today );
      $this->assertEquals( 0, $importErrorsForVendor1TodayMovie->count(), 'There should be no LogImportError for vendor1 Movie' );

      $importErrorsForVendor2TodayPoi = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor2[ 'id' ], 'Poi', $today, $today );
      $this->assertEquals( 0, $importErrorsForVendor2TodayPoi->count(), 'There should be no LogImportError for vendor2 poi today' );
      
      // today and yesterday
      $importErrorsForVendor1TodayPoi = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor1[ 'id' ], 'Poi', $yesterday, $today );
      $this->assertEquals( 3, $importErrorsForVendor1TodayPoi->count(), 'There should be 2 LogImportError for vendor1 poi today and yesterday' );

      $importErrorsForVendor2TodayEvent = Doctrine::getTable( 'LogImportError' )
                                                ->getLogImportErrors( $vendor2[ 'id' ], 'Event', $yesterday, $today );
      $this->assertEquals( 1, $importErrorsForVendor2TodayEvent->count(), 'There should be 1 LogImportError for vendor2 event today and yesterday' );

      
  }

}