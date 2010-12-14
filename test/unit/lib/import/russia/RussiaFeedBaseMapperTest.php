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

    public function testGetFormattedAndFixedPhoneNnumber()
    {

        // These are some of the numbers reported by Nokia
        $moscowVendor = Doctrine::getTable('Vendor')->findOneByCity( 'moscow' );
        $russianFeedBaseMapper = new RussiaFeedBaseMapperMock( $moscowVendor, array( 'phone' => array( '495' ) ) );
        $this->assertEquals( '+7 921 975 3858', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '8 921 975 3858' ) );
        $this->assertEquals( '+7 813 787 4398', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '881 378 7439 8' ) );
        $this->assertEquals( '+7 495 234 3937', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '+7495 234 3937' ) );
        $this->assertEquals( '+7 499 243 1704', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '+749 924 3170 4' ) );
        $this->assertEquals( '+7 499 111 1112', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '849 911 1111 2' ) );

        // test for saint petersburg From the FEED
        $russianFeedBaseMapper = new RussiaFeedBaseMapperMock( Doctrine::getTable( 'Vendor' )->findOneByCity( 'saint petersburg' ), array( 'phone' => array( 'areacode' => '812' ) ) );
        $this->assertEquals( '+7 812 710 4257', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '710 4257 (кассы)' ) );
        $this->assertEquals( '+7 812 272 3361', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '272-33-61' ) );
        $this->assertEquals( '+7 960 246 6370', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '+7 960 246 6370' ) );
        $this->assertEquals( '+7 812 230 0845', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '230 0845' ) );
        $this->assertEquals( '+7 921 097 2817', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '8 921 097 2817' ) );
        $this->assertEquals( '+7 812 337 6837', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '337 6837, 337 6838' ) );
        $this->assertEquals( '+7 812 275 5497', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '275 5497, 8 901 304 2357' ) );
        $this->assertEquals( '+7 911 913 7467', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '8 911 913 7467' ) );
        $this->assertEquals( '+7 904 618 6301', $russianFeedBaseMapper->getFormattedAndFixedPhoneItnlNumber( '8 904 618 6301' ) );


        
    }
}

class RussiaFeedBaseMapperMock extends RussiaFeedBaseMapper
{
    /**
     *  Override to Skip any validation errors
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct(Vendor $vendor, $params) {

        $this->vendor = $vendor;
        $this->params = $params;

    }
    
    public function getFormattedAndFixedPhoneItnlNumber( $phoneNumber )
    {
        return stringTransform::formatPhoneNumber( $this->getFormattedAndFixedPhone( $phoneNumber ), $this->vendor['inernational_dial_code']);
    }
}