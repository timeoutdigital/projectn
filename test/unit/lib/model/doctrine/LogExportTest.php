<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogExport Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class LogExportTest extends PHPUnit_Framework_TestCase
{
   /**
   *
   * @var Doctrine_Record
   */
  private $LogExport;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->logExport = ProjectN_Test_Unit_Factory::add( 'logexport', array( 'created_at' => '2011-01-01 00:00:00' ) );
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

  public function testGetName()
  {
      $this->assertEquals( '2011-01-01 00:00:00 / test city_en-GB (success)', $this->logExport[ 'name' ] );
  }
}