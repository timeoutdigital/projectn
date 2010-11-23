<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test for Chicago Base Mapper
 *
 * @package test
 * @subpackage chicago.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class ChicagoFeedBaseMapperTest extends PHPUnit_Framework_TestCase
{
    public function testCleanTheData()
    {
        $mapper = new ChicagoFeedBaseMapperCleanDataTestMock();
        $xmlNodes = $mapper->getXMLData();

        // have 7 ROW nodes?
        $this->assertEquals( 7, count( $xmlNodes->ROW ) );

        // assert contents
        $node = $xmlNodes->ROW[0];
        $this->assertStringStartsWith('Modern-day Turkey isn’t the idea here, so if you’re looking for contemporary Istanbul', (string)$node->body, 'Clean should leave behind valid ASCII chars, hence quote should be there');
        $this->assertEquals( 'New 2010 â Alfred and noteworthy', (string)$node->category, 'cleanTheData should have cleant he weird chars');
    }
}

/**
 * This class is created to Test cleanTheContents() only
 */
class ChicagoFeedBaseMapperCleanDataTestMock extends ChicagoFeedBaseMapper
{
    private $data;
    
    public function  __construct( ) {
        $this->ftpGetDataAndCleanData();
    }

    protected function  ftpGetDataAndCleanData($requireCleaning = true) {

        $testFileName = TO_TEST_DATA_PATH . '/chicago/short_toc_ed_for_base_mapper.xml';

        $contents = file_get_contents( $testFileName );

        $this->data = $this->cleanTheContents( $contents );
    }

    public function getXMLData()
    {
        return simplexml_load_string( $this->data );
    }
    

}