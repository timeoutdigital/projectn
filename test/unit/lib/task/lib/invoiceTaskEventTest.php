<?php

require_once dirname(__FILE__).'/invoiceTaskBase.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class invoiceTaskEventTest extends invoiceTaskBase
{
    public function testEventInvoice()
    {
        // Run the invoiceTask and capture the output.
        $this->options['type'] = 'event';
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
            $xml = simplexml_load_file( $this->options['path'] . 'export_'.date( 'Ymd', $date ).'/event/test_city.xml' );

            // Check the date is correct.
            $this->assertEquals( date( 'Y-m-d', $date ), $sheet[ $k ]['DATE'] );

            // Check the city name is correct.
            $this->assertEquals( 'Test_city', $sheet[ $k ]['CITY'] );

            // Check that 'EXISTING' is the same as the previous days 'NEW'.
            $this->assertEquals( $k < 1 ? 0 : $sheet[ $k-1 ]['NEW'], $sheet[ $k ]['EXISTING'] );

            // Check that every node in the xml was counted as 'provided', as this indicates how many nodes are in the feed.
            $this->assertEquals( count( $xml->xpath( '//event' ) ), $sheet[ $k ]['PROVIDED'] );

            // Check that erroneous and duplicate nodes are not included as 'NEW' (and therefore not billed for).
            $this->assertEquals( count( $xml->xpath( '//event' ) ) - $day['expectedNonBillable'], $sheet[ $k ]['NEW'] );

            // Check that there is a duplicate node in the XML test file.
            $this->assertEquals( 2, count( $xml->xpath( '//event[@id="EXA000000000000000000000000000001"]' ) ), 'Please include a duplicate in your test data.' );

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