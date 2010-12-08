<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test class for australia base mapper
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevakumarathasan@timout.com>
 *
 * @version 1.0.0
 */
class australiaBaseMapperTest extends PHPUnit_Framework_TestCase
{

    private $vendor;
    private $params;
    
    public function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('sydney');
        $this->params = array( 'type' => 'base', 'ftp' => array(
                                                            'classname' => 'FTPClientMock',
                                                            'username' => 'test',
                                                            'password' => 'test',
                                                            'src' => '',
                                                            'dir' => '/',
                                                            'file' => TO_TEST_DATA_PATH . '/sydney/sydney_base_mapper.xml'
                                                            )
            );
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testConstructorAllValidData()
    {
        $mapper = new australiaBaseMapperMock( $this->vendor, $this->params );
        $importer = new Importer( );
        $importer->addDataMapper( $mapper );
        $importer->run();

        $this->assertTrue( $mapper->getSimpleXMLElement() instanceof SimpleXMLElement );
    }

    public function testConstructorExceptionInvalidVendor()
    {
        $this->setExpectedException( 'Exception' );
        $mapper = new australiaBaseMapperMock( null, $this->params );
    }
    public function testConstructorExceptionInvalidParams()
    {
        $this->setExpectedException( 'Exception' );
        $mapper = new australiaBaseMapperMock( $this->vendor, array() );
    }

    /* Syndey provides times in 12 hr format with AM/PM */
    public function testExtractDateTimeSydney()
    {
        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'sydney' );
        $mapper = new australiaBaseMapperMock( $this->vendor, $this->params );

        $this->assertEquals( '2000-01-01 00:00:00', $mapper->proxyExtractDateTime( '1/01/2000 12:00:00 AM' )->format( 'Y-m-d H:i:s' ), 'midnight' );
        $this->assertEquals( '2000-01-01 12:00:00', $mapper->proxyExtractDateTime( '1/01/2000 12:00:00 PM' )->format( 'Y-m-d H:i:s' ), 'midday' );
        $this->assertEquals( '2008-03-05 16:45:00', $mapper->proxyExtractDateTime( '5/03/2008 4:45:00 PM'  )->format( 'Y-m-d H:i:s' ) );
        $this->assertEquals( '2010-03-03 12:56:00', $mapper->proxyExtractDateTime( '3/03/2010 12:56:00 PM' )->format( 'Y-m-d H:i:s' ) );
    }

    /* Melbourne provides times in 24 hr format and AM/PM is removed to avoid DateTime() throwing an exception */
    public function testExtractDateTimeMelbourne()
    {
        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'melbourne' );
        $this->params = array( 'type' => 'base', 'curl' => array( 'classname' => 'CurlMock' ) );

        $mapper = new australiaBaseMapperMock( $this->vendor, $this->params );

        $this->assertEquals( '2000-01-01 00:00:00', $mapper->proxyExtractDateTime( '1/01/2000 00:00:00 AM' )->format( 'Y-m-d H:i:s' ), 'midnight' );
        $this->assertEquals( '2000-01-01 12:00:00', $mapper->proxyExtractDateTime( '1/01/2000 12:00:00 PM' )->format( 'Y-m-d H:i:s' ), 'midday' );
        
        $this->assertEquals( '2008-03-05 04:45:00', $mapper->proxyExtractDateTime( '5/03/2008 4:45:00 AM'  )->format( 'Y-m-d H:i:s' ) );
        $this->assertEquals( '2008-03-05 16:45:00', $mapper->proxyExtractDateTime( '5/03/2008 4:45:00 PM'  )->format( 'Y-m-d H:i:s' ) );
        
        $this->assertEquals( '2010-03-03 00:56:00', $mapper->proxyExtractDateTime( '3/03/2010 12:56:00 AM' )->format( 'Y-m-d H:i:s' ) );
        $this->assertEquals( '2010-03-03 12:56:00', $mapper->proxyExtractDateTime( '3/03/2010 12:56:00 PM' )->format( 'Y-m-d H:i:s' ) );

        $this->assertEquals( '2010-03-03 13:56:00', $mapper->proxyExtractDateTime( '3/03/2010 13:56:00 PM' )->format( 'Y-m-d H:i:s' ) );
    }
    
}

/**
 * Mocking Basemapper to write a public fuction to Get the XML loaded to test
 */
class australiaBaseMapperMock extends australiaBaseMapper
{
    public function getSimpleXMLElement()
    {
        return $this->feed;
    }

    protected function _getTheLatestFileName( $rawFtpListingOutput, $xmlFileName )
    {
        return $xmlFileName;
    }

    public function proxyExtractDateTime( $dateString )
    {
        return $this->extractDateTime( $dateString );
    }

    protected function _loadXMLFromFeed( $vendor, $params ){}
}