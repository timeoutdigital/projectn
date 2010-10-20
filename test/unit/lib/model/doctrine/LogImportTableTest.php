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

}