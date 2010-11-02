<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Vendor Model
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

class VendorTableTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testFindAllVendorsInAlphabeticalOrder()
    {
        $vendorZ = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'Z City name' ) );
        $vendorA = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'A City name' ) );
        $vendorB = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'B City name' ) );

        $vendors = Doctrine::getTable( 'Vendor' )->findAllVendorsInAlphaBeticalOrder();

        $this->assertEquals( 3, $vendors->count(), 'There should be 3 Vendors in DB');
        $this->assertEquals( 'A City name', $vendors[0]['city'], 'First vendor should be A City name after Sorting A-Z');
        $this->assertEquals( 'B City name', $vendors[1]['city'], 'Second vendor is B City name');

    }
}