<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for widgetFormEventVendorCategoryChoice
 *
 * @package test
 * @subpackage doctrine.form.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class widgeFormVendorEventCategoryChoiceTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testWidget()
  {
    $ny   = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'ny' ) );
    $event1 = ProjectN_Test_Unit_Factory::get( 'Event', null, false );
    $event1[ 'Vendor' ] = $ny;
    $event1->addVendorCategory( 'ny foo', $ny['id'] );
    $event1->addVendorCategory( 'ny bar', $ny['id'] );
    $event1->addVendorCategory( 'ny baz', $ny['id'] );
    $event1->save();

    $chicago = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'chicago' ) );
    $event2    = ProjectN_Test_Unit_Factory::get( 'Event', null, false );
    $event2[ 'Vendor' ] = $chicago;
    $event2->addVendorCategory( 'chicago foo', $chicago['id'] );
    $event2->addVendorCategory( 'chicago bar', $chicago['id'] );
    $event2->addVendorCategory( 'chicago baz', $chicago['id'] );
    $event2->save();

    $this->assertEquals( 6, Doctrine::getTable( 'VendorEventCategory' )->findAll()->count() );

    $widget = new widgetFormEventVendorCategoryChoice( array( 'record' => $event1 ) );
    $html   = $widget->render( 'Name', '3');

    $this->assertRegexp( ':value="1".*?ny foo:', $html );
    $this->assertRegexp( ':value="2".*?ny bar:', $html );
    $this->assertRegexp( ':value="3".*?ny baz:', $html );
    $this->assertRegexp( ':selected="selected".*?ny baz:', $html );

    $widget = new widgetFormEventVendorCategoryChoice( array( 'record' => $event2 ) );
    $html   = $widget->render( 'Name', '5');

    $this->assertRegexp( ':value="4".*?chicago foo:', $html );
    $this->assertRegexp( ':value="5".*?chicago bar:', $html );
    $this->assertRegexp( ':value="6".*?chicago baz:', $html );
    $this->assertRegexp( ':selected="selected".*?chicago bar:', $html );
  }
}
