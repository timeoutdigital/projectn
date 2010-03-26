<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Vendor Event Category Table Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class VendorEventCategoryTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testDoesNotHaveDuplicateVendorEventCategories()
  {
    $vendorEventCategory1 = ProjectN_Test_Unit_Factory::add( 'VendorEventCategory' );
    $vendorEventCategory2 = ProjectN_Test_Unit_Factory::add( 'VendorEventCategory' );

    $vendorEventCategoryTable = Doctrine::getTable( 'VendorEventCategory' );
    $this->assertEquals( 1, $vendorEventCategoryTable->count() );

    $newName = 'Rename vendor category 1';
    $vendorEventCategory1[ 'name' ] = $newName;
    $vendorEventCategory1->save();

    $vendorEventCategory = $vendorEventCategoryTable->findOneById( 1 );
    $this->assertEquals( $newName, $vendorEventCategory[ 'name' ] );
  }
}
?>