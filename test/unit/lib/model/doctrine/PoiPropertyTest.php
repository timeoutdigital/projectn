<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Poi Property Model
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
class PoiPropertyTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var PoiProperty
   */
  protected $object;

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

  /**
   *
   */
  public function testPropertyIsUpdatedNotDuplicated()
  {
    $this->markTestIncomplete();
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi->addProperty( 'one', 'eins' );
    $poi->addProperty( 'two', 'zwei' );
    $poi->addProperty( 'three', 'drei' );
    $poi->save();

    $this->assertEquals( 3, Doctrine::getTable('PoiProperty')->count() );

    $poi['email'] = 'another@email.com';
    $poi->addProperty( 'one', 'eins' );
    $poi->addProperty( 'two', 'zwei' );
    $poi->addProperty( 'three', 'drei' );
    $poi->save();

    $this->assertEquals( 3, Doctrine::getTable('PoiProperty')->count() );
  }
}
?>
