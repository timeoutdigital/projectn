<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * test for Bucharest Movie mapper
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

class bucharestMovieMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $params;

    public function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'bucharest' );
        $this->params = array( 'type' => 'movie', 'curl' => array( 'classname' => 'CurlMock', 'src' => TO_TEST_DATA_PATH . '/bucharest/movies.xml' ) );

        $this->_dummyPoi();
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapMovies()
    {
        $this->assertEquals( 8 , Doctrine::getTable( 'Movie' )->count() );
        
    }
}