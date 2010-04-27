<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API Bars And Pubs Mapper.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class removeCommaLondonFromEndOfStringTest extends PHPUnit_Framework_TestCase
{
  public function test()
  {
    $fix = new removeCommaLondonFromEndOfString( 'Guildhall Library, Aldermanbury, London ' );
    $this->assertEquals( 'Guildhall Library, Aldermanbury', $fix->getFixedString() );
  }
}
