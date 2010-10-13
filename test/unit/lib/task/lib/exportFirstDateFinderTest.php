<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for the eventPlaceIdsShouldExistInPoiXml
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class eachPoiHasAtleastOneVendorCategoryTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $exportType    = 'poi';
    $pathToExports = TO_TEST_DATA_PATH . '/file';

    $finder = new exportFirstDateFinder( $exportType, $pathToExports );
    $dates  = $finder->run();

    $londonFirstExportDate = new DateTime( '2010-04-20' );
    $this->assertEquals( '2010-04-20', $londonFirstExportDate->format( 'Y-m-d' ), 
                         'Checking DateTime object works as I expect it to.' );

    $this->assertEquals( $londonFirstExportDate, $dates[ 'london' ] );
    $this->assertEquals( $londonFirstExportDate, $dates[ 'singapore' ], 'singapore exported same day as london in our fixture' );

    $russiaFirstExportDate = new DateTime( '2010-08-19' );
    $this->assertEquals( $russiaFirstExportDate, $dates[ 'almaty' ] );
    $this->assertEquals( $russiaFirstExportDate, $dates[ 'moscow' ] );
    $this->assertEquals( $russiaFirstExportDate, $dates[ 'saint petersburg' ] );

    $istanbulFirstExportDate = new DateTime( '2010-10-05' );
    $this->assertEquals( $istanbulFirstExportDate, $dates[ 'istanbul' ] );
  }
}
