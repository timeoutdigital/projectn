<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for validatorVendorEventCategoryChoice
 *
 * @package test
 * @subpackage validator.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class validatorVendorEventCategoryChoiceTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testHasCorrectChoices()
  {
    $ny    = $this->createVendorFor( 'ny' );
    $nyEvent = $this->createEventFor(    $ny );
    $nyEvent->addVendorCategory( 'ny foo', $ny['id'] );
    $nyEvent->addVendorCategory( 'ny bar', $ny['id'] );
    $nyEvent->addVendorCategory( 'ny baz', $ny['id'] );
    $nyEvent->save();
     
    $chicago    = $this->createVendorFor( 'chicago' );
    $chicagoEvent = $this->createEventFor(    $chicago );
    $chicagoEvent->addVendorCategory( 'chicago foo', $chicago['id'] );
    $chicagoEvent->addVendorCategory( 'chicago bar', $chicago['id'] );
    $chicagoEvent->addVendorCategory( 'chicago baz', $chicago['id'] );
    $chicagoEvent->save();

    $validatorNy = new validatorVendorEventCategoryChoice( array( 'event' => $nyEvent ) );

    $expectedValues = array( 1, 2, 3);
    $this->assertEquals( $expectedValues, $validatorNy->getChoices() );

    $validatorChicago = new validatorVendorEventCategoryChoice( array( 'event' => $chicagoEvent ) );

    $expectedValues = array( 4, 5, 6);
    $this->assertEquals( $expectedValues, $validatorChicago->getChoices() );
  }

  private function createVendorFor( $city )
  {
    return ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => $city ) );
  }

  private function createEventFor( $vendor )
  {
    //don't auto generate vendor category on the db (third param)
    $event = ProjectN_Test_Unit_Factory::get( 'Event', null, false );
    $event['Vendor'] = $vendor;
    return $event;
  }
}
