<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for RecordFieldOverride Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class RecordFieldOverrideTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Event
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->object = Doctrine::getTable('RecordFieldOverridePoi');

    $this->record = ProjectN_Test_Unit_Factory::add( 'Poi' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * test if the add property adds the properties
   */
  public function testAddOverride()
  {
    $this->createPoiOverride( $this->record, 'poi_name', false );
    $this->createPoiOverride( $this->record, 'poi_name', true );
    $overrides = $this->object->findAll();
    $this->assertEquals( 1, $overrides->count() );
    $this->assertTrue( $overrides[ 0 ][ 'is_active' ] );

    $this->createPoiOverride( $this->record, 'poi_name', false );
    $overrides = $this->object->findAll();
    $this->assertEquals( 1, $overrides->count() );
    $this->assertFalse( $overrides[ 0 ][ 'is_active' ] );
  }

  private function createPoiOverride( $poi, $field, $active )
  {
    $override = new recordFieldOverridePoi();
    $override[ 'record_id' ] = $poi[ 'id' ];
    $override[ 'field' ] = $field;
    $override[ 'received_value' ] = 'aaa';
    $override[ 'edited_value' ] = 'bbb';
    $override[ 'is_active' ] = $active;
    $override->save();
  }
}
?>
