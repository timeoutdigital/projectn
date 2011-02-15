<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogTask Model
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

class LogTaskTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var Poi
   */
  protected $object;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();

      $this->object = ProjectN_Test_Unit_Factory::get( 'logtask' );
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

  /*
   * test if the add params adds params successfully and does not duplicate them
   */
  public function testAddParam()
  {
    $this->object->addParam( 'test param lookup', 'test param value' );
    $this->object->addParam( 'test param lookup 2', 'test param value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('LogTask')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test param lookup', $this->object[ 'LogTaskParam' ][ 0 ][ 'name' ] );
    $this->assertEquals( 'test param value', $this->object[ 'LogTaskParam' ][ 0 ][ 'value' ] );

    $this->assertEquals( 'test param lookup 2', $this->object[ 'LogTaskParam' ][ 1 ][ 'name' ] );
    $this->assertEquals( 'test param value 2', $this->object[ 'LogTaskParam' ][ 1 ][ 'value' ] );

    $this->object->addParam( 'test param lookup', 'test param value' );
    $this->object->addParam( 'test param lookup 2', 'test param value 2' );
    $this->object->save();

    $poi = Doctrine::getTable('LogTask')->findOneById( $this->object['id'] );

    $this->assertEquals(2, count($poi['LogTaskParam']) );
  }

}
