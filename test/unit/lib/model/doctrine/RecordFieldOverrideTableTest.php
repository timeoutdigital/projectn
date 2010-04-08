<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Record Field Override Table Model
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
class RecordFieldOverrideTableTest extends PHPUnit_Framework_TestCase
{


  /**
   *
   * @var Object
   */
  private $_record;

  /**
   * @var RecordFieldOverrideTableTest
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

    $this->_record = ProjectN_Test_Unit_Factory::add( 'Poi' );

    $this->createPoiOverride( $this->_record, 'poi_name', true );
    $this->createPoiOverride( $this->_record, 'poi_name', false );
    $this->createPoiOverride( $this->_record, 'poi_name', false );

    $this->createPoiOverride( $this->_record, 'city', true );
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
   * test findActiveOverrideForRecord() returns the right string
   */
  public function testFindActiveOverrideForRecord()
  {
    $override = $this->object->findActiveOverrideForRecord( $this->_record );
    $this->assertEquals( 2, count( $override ) );
  }

  /**
   * test findActiveOverrideForRecord() returns the right string
   */
  public function testFindActiveOverrideForRecordByField()
  {
    $override = $this->object->findActiveOverrideForRecordByField( $this->_record, 'poi_name' );
    $this->assertEquals( 1, count( $override ) );
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
