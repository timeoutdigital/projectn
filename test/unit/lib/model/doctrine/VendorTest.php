<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

/**
 * Test class for Vendor model.
 * Generated by PHPUnit on 2010-01-07 at 12:14:28.
 *
 * @author Clarence Lee <clarencelee@timeout.com>
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
      $this->vendor->setCity('test');
      $this->vendor->setLanguage('english');
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
    $this->assertEquals( 'test_english', $this->vendor->getName() );
  }
}