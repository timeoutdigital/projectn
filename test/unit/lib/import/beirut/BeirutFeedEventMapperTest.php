<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Beirut Event Mapper test
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class BeirutFeedEventMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    private $params;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        // Set up vendor and params
        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'beirut' );
        $this->params = array( 'type' => 'event', 'curl' => array(
                                                                'classname' => 'CurlMock',
                                                                'src' => TO_TEST_DATA_PATH . '/beirut/event.xml',
                                                                ) );

        // Import Data
        $importer = new Importer( );
        $importer->addDataMapper( new BeirutFeedVenueMapper( $this->vendor, $this->params ) );
        $importer->run();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapEvent()
    {
        $this->assertEquals( 6, Doctrine::getTable( 'Event' )->count() );
    }
}