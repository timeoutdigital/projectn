<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for UAE Feed Base Mapper
 *
 * @package test
 * @subpackage uae.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.1.0
 *
 */

class UAEFeedBaseMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );
    }

    protected function tearDown(){}

    public function testInvalidVendor()
    {
        $this->setExpectedException( 'UAEMapperException' );
        new UAEFeedBaseMapper( new stdclass(), array() );
    }

    public function testInvalidParams()
    {
        $this->setExpectedException( 'UAEMapperException' );
        new UAEFeedBaseMapper( $this->vendor, array() );
    }
}