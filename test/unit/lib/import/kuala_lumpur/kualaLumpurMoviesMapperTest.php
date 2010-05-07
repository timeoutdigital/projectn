<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Events mapper
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class kualaLumpurMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en',
      'inernational_dial_code' => '+60',
      ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_events.xml' );
    $this->runImport();

    $this->events = Doctrine::getTable( 'Event' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
  }

  private function runImport()
  {
    $importer = new Importer();
    $importer->addDataMapper( new kualaLumpurEventsMapper( $this->vendor, $this->xml ) );
    //$importer->addLogger( new echoingLogger( ));
    $importer->run();
  }
}
