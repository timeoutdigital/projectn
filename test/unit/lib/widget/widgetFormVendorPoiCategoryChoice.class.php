<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for widgetFormPoiVendorCategoryChoice
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
class widgeFormVendorPoiCategoryChoiceTest extends PHPUnit_Framework_TestCase
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
    $poi1 = ProjectN_Test_Unit_Factory::get( 'Poi', null, false );
    $poi1[ 'Vendor' ] = $ny;
    $poi1->addVendorCategory( 'ny foo', $ny['id'] );
    $poi1->addVendorCategory( 'ny bar', $ny['id'] );
    $poi1->addVendorCategory( 'ny baz', $ny['id'] );
    $poi1->save();

    $chicago = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => 'chicago' ) );
    $poi2    = ProjectN_Test_Unit_Factory::get( 'Poi', null, false );
    $poi2[ 'Vendor' ] = $chicago;
    $poi2->addVendorCategory( 'chicago foo', $chicago['id'] );
    $poi2->addVendorCategory( 'chicago bar', $chicago['id'] );
    $poi2->addVendorCategory( 'chicago baz', $chicago['id'] );
    $poi2->save();

    $this->assertEquals( 6, Doctrine::getTable( 'VendorPoiCategory' )->findAll()->count() );

    $widget = new widgetFormPoiVendorCategoryChoice( array( 'record' => $poi1 ) );
    $html   = $widget->render( 'Name', '3');

    $this->assertRegexp( ':value="1".*?ny foo:', $html );
    $this->assertRegexp( ':value="2".*?ny bar:', $html );
    $this->assertRegexp( ':value="3".*?ny baz:', $html );
    $this->assertRegexp( ':selected="selected".*?ny baz:', $html );

    $widget = new widgetFormPoiVendorCategoryChoice( array( 'record' => $poi2 ) );
    $html   = $widget->render( 'Name', '5');

    $this->assertRegexp( ':value="4".*?chicago foo:', $html );
    $this->assertRegexp( ':value="5".*?chicago bar:', $html );
    $this->assertRegexp( ':value="6".*?chicago baz:', $html );
    $this->assertRegexp( ':selected="selected".*?chicago bar:', $html );
  }
}
