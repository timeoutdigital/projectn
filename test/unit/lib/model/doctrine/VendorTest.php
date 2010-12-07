<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Vendor Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class VendorTest extends PHPUnit_Framework_TestCase
{


  protected $object;

  private $vendor;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    try
    {
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->vendor = new Vendor();
      $this->vendor['city'] = 'test';
      $this->vendor['language'] = 'en-US';
      $this->vendor['time_zone'] = 'Asia/Singapore';
      $this->vendor['inernational_dial_code'] = '+65';
      $this->vendor['airport_code'] = 'XXX';
      $this->vendor['country_code'] = 'XX';
      $this->vendor['country_code_long'] = 'XXX';
      $this->vendor['geo_boundries'] = 'XXX';
      $this->vendor->save();

    }
    catch(PDOException $e)
    {
      echo $e->getMessage();
    }
  }


  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    //Close DB connection
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Test if getName() returns the concatenated string out of city_language
   *
   */
  public function testGetName()
  {
    $this->assertEquals( 'test_en-US', $this->vendor->getName() );
  }

  /**
   * Test if testGetUtcOffset() returns the correct utc offset string
   */
  public function testGetUtcOffset()
  {
    $this->assertEquals( '+08:00', $this->vendor->getUtcOffset( date( 'Y-m-d' ) ) );
  }

  /**
   * Test to see if the isWithinBoundaries() catching outside geocode
   */
  public function testIsWithinBoundariesOutboundaries()
  {
      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'geo_boundries' => '0;5;5;10') );
      $this->assertFalse( $vendor->isWithinBoundaries( 7, 9 ), 'Latitude is out of geo boundary' );
      $this->assertFalse( $vendor->isWithinBoundaries( 1,10.1 ), 'Longitude is out of geo boundary' );
      $this->assertFalse( $vendor->isWithinBoundaries( -01.58,10.1 ), 'Lat/Long is out of geo boundary' );
  }

  /**
   * Test to see when boundaries are valid, that isWithinBoundaries() return TRUE
   */
  public function testIsWithinBoundariesValid()
  {
      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'geo_boundries' => '1;-5;2;-2') );
      $this->assertTrue( $vendor->isWithinBoundaries( 1.5, -3 ));
  }
}
