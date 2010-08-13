<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for the eventsShouldNotHaveDuplicateOccurrences
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class invoiceTaskTest extends PHPUnit_Framework_TestCase
{
    private $options;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new invoiceTask( new sfEventDispatcher, new sfFormatter );
        
        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
        $this->options['csv'] = 'true';
        $this->options['path'] = TO_TEST_DATA_PATH . '/invoice/';
        $this->options['STDERR'] = 'false';
        
        $this->populateDatabase();
    }

    protected function tearDown()
    {
        parent::tearDown();
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    protected function addModel( $model, array $properties = array(), array $links = array() )
    {
        $m = new $model;
        $m->merge( $properties );
        foreach( $links as $linkModel => $idArray ) $m->link( $linkModel, $idArray );
        $m->save();
    }

    protected function populateDatabase()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
        ProjectN_Test_Unit_Factory::createDatabases();
        
        $v = ProjectN_Test_Unit_Factory::add( 'Vendor' );

        $vendorSampleCats = array( 'A','B','C','D','E','F','G' );
        foreach( $vendorSampleCats as $catName )
        {
            $this->addModel( 'VendorPoiCategory',   array( 'name' => $catName, 'vendor_id' => $v['id'] ) );
            $this->addModel( 'VendorEventCategory', array( 'name' => $catName, 'vendor_id' => $v['id'] ) );
        }

        $this->uiCatgeories = array( 'Film', 'Eating & Drinking', 'Around Town', 'Music', 'Stage', 'Nightlife', 'Art' );
        for( $x=1; $x<count( $this->uiCatgeories )+1; $x++ )
        {
            $this->addModel( 'UiCategory',
                array( 'name' => $this->uiCatgeories[$x-1] ),
                array( 'VendorPoiCategory'   => array( $x ),
                       'VendorEventCategory' => array( $x )
                ));
        }
    }

    protected function runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";
        
        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }

    protected function parseCSV( $csv )
    {
        $data = explode( PHP_EOL, trim( $csv ) );
        $headers = explode( ',', array_shift( $data ) );

        foreach( $data as $datarow )
        {
            $columns = explode( ',', $datarow );
            for( $x=0; $x<count( $columns ); $x++ )
                $row[ $headers[ $x ] ] = $columns[ $x ];

            $sheet[] = $row;
        }

        return $sheet;
    }

    public function testInvoiceTask()
    {
        // Run the invoiceTask and capture the output.
        $this->options['type'] = 'poi';
        $this->options['city'] = 'test_city';
        $sheet = $this->parseCSV( $this->runTask() );

        // Specify the test data files you're expecting to be in the report and the amount of erroneous records you've put in each.
        // NOTE: Failing to specify a date for which a test file exists will cause the test to fail.
        $days[] = array( 'date' => '2010-07-16', 'expectedNonBillable' => 3, 'expectedInvalidCategory' => 2 );
        $days[] = array( 'date' => '2010-07-17', 'expectedNonBillable' => 2, 'expectedInvalidCategory' => 0 );

        // Check the spreadsheet has expected total days worth of data.
        $this->assertEquals( count( $days ), count( $sheet ) );

        // Cycle through days.
        foreach( $days as $k => $day )
        {
            // Parse Date.
            $date = strtotime( $day['date'] );

            // Load the XML test file.
            $xml = simplexml_load_file( $this->options['path'] . 'export_'.date( 'Ymd', $date ).'/poi/test_city.xml' );

            // Check the date is correct.
            $this->assertEquals( date( 'Y-m-d', $date ), $sheet[ $k ]['DATE'] );

            // Check the city name is correct.
            $this->assertEquals( 'Test_city', $sheet[ $k ]['CITY'] );

            // Check that 'EXISTING' is the same as the previous days 'NEW'.
            $this->assertEquals( $k < 1 ? 0 : $sheet[ $k-1 ]['NEW'], $sheet[ $k ]['EXISTING'] );

            // Check that every node in the xml was counted as 'provided', as this indicates how many nodes are in the feed.
            $this->assertEquals( count( $xml->xpath( '//entry' ) ), $sheet[ $k ]['PROVIDED'] );

            // Check that erroneous and duplicate nodes are not included as 'NEW' (and therefore not billed for).
            $this->assertEquals( count( $xml->xpath( '//entry' ) ) - $day['expectedNonBillable'], $sheet[ $k ]['NEW'] );

            // Check that there is a duplicate node in the XML test file.
            $this->assertEquals( 2, count( $xml->xpath( '//entry[@vpid="EXA000000000000000000000000000001"]' ) ), 'Please include a duplicate in your test data.' );

            // Check that the duplicate node was only included once in the report as 'new'.
            $this->assertEquals( 1, $sheet[0][ $this->uiCatgeories[  $k  ] ] );

            // Check that each UI Category mapped exactly once only.
            foreach( $this->uiCatgeories as $uiCategory ) $this->assertEquals( 1, $sheet[ $k ][ $uiCategory ] );

            // Check that the entries with missing or invalid vendor category are listed as 'NOCAT'.
            $this->assertEquals( $day['expectedInvalidCategory'], $sheet[ $k ]['NOCAT'] );
        }
        
        //echo count( $xml->xpath( '//entry/version/content[vendor-category/text()="A"]' ) ) . PHP_EOL;
        //print_r( $sheet );
    }
}