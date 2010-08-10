<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for validatorVendorPoiCategoryChoice
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
class validatorVendorPoiCategoryChoiceTest extends PHPUnit_Framework_TestCase
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
    $nyPoi = $this->createPoiFor(    $ny );
    $nyPoi->addVendorCategory( 'ny foo', $ny['id'] );
    $nyPoi->addVendorCategory( 'ny bar', $ny['id'] );
    $nyPoi->addVendorCategory( 'ny baz', $ny['id'] );
    $nyPoi->save();

    $chicago    = $this->createVendorFor( 'chicago' );
    $chicagoPoi = $this->createPoiFor(    $chicago );
    $chicagoPoi->addVendorCategory( 'chicago foo', $chicago['id'] );
    $chicagoPoi->addVendorCategory( 'chicago bar', $chicago['id'] );
    $chicagoPoi->addVendorCategory( 'chicago baz', $chicago['id'] );
    $chicagoPoi->save();

    $validatorNy = new validatorVendorPoiCategoryChoice( array( 'vendor_id' => $ny[ 'id' ]  ) );

    $expectedValues = array( 1, 2, 3);
    $this->assertEquals( $expectedValues, $validatorNy->getChoices() );

    $validatorChicago = new validatorVendorPoiCategoryChoice( array( 'vendor_id' => $chicago[ 'id' ] ) );

    $expectedValues = array( 4, 5, 6);
    $this->assertEquals( $expectedValues, $validatorChicago->getChoices() );
  }

  private function createVendorFor( $city )
  {
    return ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'city' => $city ) );
  }

  private function createPoiFor( $vendor )
  {
    //don't auto generate vendor category on the db (third param)
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi', null, false );
    $poi['Vendor'] = $vendor;
    return $poi;
  }
}
