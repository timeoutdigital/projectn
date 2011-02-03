<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogImport Model
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

class LogImportTest extends PHPUnit_Framework_TestCase
{  
  /**
   *
   * @var Doctrine_Record
   */
  private $LogImport;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->LogImport = ProjectN_Test_Unit_Factory::add( 'logimport', array( 'created_at' => '2011-01-01 00:00:00' ) );

      ProjectN_Test_Unit_Factory::add( 'logimportcount', array( 'count' => 55, 'operation' => 'updated' ) );
      ProjectN_Test_Unit_Factory::add( 'logimportcount', array( 'count' => 54, 'operation' => 'insert' ) );
      ProjectN_Test_Unit_Factory::add( 'logimportcount', array( 'count' => 53, 'operation' => 'existing' ) );
      ProjectN_Test_Unit_Factory::add( 'logimportcount', array( 'count' => 52, 'operation' => 'failed' ) );
      ProjectN_Test_Unit_Factory::add( 'logimportcount', array( 'count' => 51, 'operation' => 'delete' ) );

      $this->LogImport->refresh( true );
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

  public function testGetCountFor()
  {
      $this->assertEquals( 55, $this->LogImport->getCountFor( 'Poi', array( 'updated' ) ) );
      $this->assertEquals( 54, $this->LogImport->getCountFor( 'Poi', array( 'insert' ) ) );
      $this->assertEquals( 53, $this->LogImport->getCountFor( 'Poi', array( 'existing' ) ) );
      $this->assertEquals( 52, $this->LogImport->getCountFor( 'Poi', array( 'failed' ) ) );
      $this->assertEquals( 51, $this->LogImport->getCountFor( 'Poi', array( 'delete' ) ) );

      //all together (default)
      $this->assertEquals( 265, $this->LogImport->getCountFor( 'Poi') );

      //and some handpicked
      $this->assertEquals( 106, $this->LogImport->getCountFor( 'Poi', array( 'failed', 'insert' ) ) );
  }

  public function testGetDate()
  {
      $this->assertEquals( '2011-01-01', $this->LogImport[ 'date' ] );
  }

  public function testGetName()
  {
      $this->assertEquals( '2011-01-01 00:00:00 / test city_en-GB (success)', $this->LogImport[ 'name' ] );
  }
}
