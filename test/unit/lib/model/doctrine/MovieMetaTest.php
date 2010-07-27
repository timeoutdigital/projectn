<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for MovieMeta Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class MovieMetaTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testSave()
  {
      $meta = new MovieMeta();
      $this->setExpectedException("Doctrine_Connection_Sqlite_Exception");
      $meta->save();

      $meta = new Meta();
      $meta['record_id'] = 1;
      $meta['lookup'] = 'foo';
      $meta['value'] = 'bar';
      $meta->save();
  }

}
