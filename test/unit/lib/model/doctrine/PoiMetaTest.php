<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for PoiMeta Model
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
class PoiMetaTest extends PHPUnit_Framework_TestCase
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

  public function testSaveWithoutLookupAndValue()
  {
    try
    {
      $meta = new PoiMeta();
      $meta->save();
    }catch (Exception $e)
    {
        $this->assertTrue( true );
        return;
    }

    $this->fail('An expected exception has not been raised.');

  }
  public function testSave()
  {
      try{
      $meta = new Meta();
      $meta['record_id'] = 1;
      $meta['lookup'] = 'foo';
      $meta['value'] = 'bar';
      $meta->save();
      }catch (Exception $e)
      {
        $this->fail('An unexpected exception has  been raised.');
      }
      $this->assertTrue( true );
  }

}
