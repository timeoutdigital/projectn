<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for Russia Feed Base Mapper
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.1.0
 *
 */

class RussiaFeedBaseMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'moscow' );
    }

    protected function tearDown(){
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testInvalidVendor()
    {
        $this->setExpectedException( 'Exception' ); // will throw 1st argument is Not Type of Vendor
        new RussiaFeedBaseMapper( new stdclass(), array() );
    }

    public function testInvalidParams()
    {
        $this->setExpectedException( 'RussiaFeedBaseMapperException' );
        new RussiaFeedBaseMapper( $this->vendor, array() );
    }
}