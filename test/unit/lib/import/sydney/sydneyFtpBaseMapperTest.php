<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

/**
 * Test class for sydney base mapper
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevakumarathasan@timout.com>
 *
 * @version 1.0.0
 */
class sydneyFtpBaseMapperTest extends PHPUnit_Framework_TestCase
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
        $mapper = new sydneyFtpBaseMapperMock( $this->vendor, $this->params );
        $importer = new Importer( );
        $importer->addDataMapper( $mapper );
        $importer->run();

        $this->assertTrue( $mapper->getSimpleXMLElement() instanceof SimpleXMLElement );
    }

    public function testConstructorExceptionInvalidVendor()
    {
        $this->setExpectedException( 'Exception' );
        $mapper = new sydneyFtpBaseMapperMock( null, $this->params );
    }
    public function testConstructorExceptionInvalidParams()
    {
        $this->setExpectedException( 'Exception' );
        $mapper = new sydneyFtpBaseMapperMock( $this->vendor, array() );
    }
    
}

/**
 * Mocking Basemapper to write a public fuction to Get the XML loaded to test
 */
class sydneyFtpBaseMapperMock extends sydneyFtpBaseMapper
{
    public function getSimpleXMLElement()
    {
        return $this->feed;
    }

    protected function _getTheLatestFileName( $rawFtpListingOutput, $xmlFileName )
    {
        return $xmlFileName;
    }
}