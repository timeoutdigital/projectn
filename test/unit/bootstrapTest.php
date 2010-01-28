<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../bootstrap/unit.php';
require_once dirname(__FILE__).'/bootstrap.php';

/**
 *
 * @author Clarence Lee
 */
class bootstrapTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Test adding a POI adds a Vendor and PoiCategory if none available to link
   * to.
   */
  public function testAddPoiCreatesVendorAndCategoryIfNecessary()
  {
    ProjectN_Test_Unit_Factory::add( 'poi', array( 'name' => 'Episode IV: A New Poi' ) );

    $pois = Doctrine::getTable( 'Poi' )->findAll();
    $this->assertEquals( 1, count( $pois ) );

    $vendors = Doctrine::getTable( 'Vendor' )->findAll();
    $this->assertEquals( 1, count( $vendors ) );

    $categories = Doctrine::getTable( 'PoiCategory' )->findAll();
    $this->assertEquals( 1, count( $categories ) );

    ProjectN_Test_Unit_Factory::add( 'poi', array( 'name' => 'Episode IV: A New Poi' ) );

    $pois = Doctrine::getTable( 'Poi' )->findAll();
    $this->assertEquals( 2, count( $pois ) );

    $vendors = Doctrine::getTable( 'Vendor' )->findAll();
    $this->assertEquals( 1, count( $vendors ) );

    $categories = Doctrine::getTable( 'PoiCategory' )->findAll();
    $this->assertEquals( 1, count( $categories ) );
  }

  /**
   * Creating a new Poi with an empty database with autoCreateRelatedObjects
   * should fail and no new Vendor or PoiCategories should exits
   */
  public function testAddPoiNoAutoCreate()
  {
    $vendors = Doctrine::getTable( 'Vendor' )->findAll();
    $this->assertEquals( 0, count( $vendors ) );

    $categories = Doctrine::getTable( 'PoiCategory' )->findAll();
    $this->assertEquals( 0, count( $categories ) );

    try
    {
      ProjectN_Test_Unit_Factory::add( 'poi', array(), false );
      $this->fail();
    }
    catch ( Exception $e ){}

    $vendors = Doctrine::getTable( 'Vendor' )->findAll();
    $this->assertEquals( 0, count( $vendors ) );

    $categories = Doctrine::getTable( 'PoiCategory' )->findAll();
    $this->assertEquals( 0, count( $categories ) );
  }

  /**
   * Test overriding field names works
   */
  public function testAddPoiWithDataOverride()
  {
    $name = 'Episode V: The Vendor Strikes Back';
    $country_code = 'GB';
    ProjectN_Test_Unit_Factory::add( 'poi', array(
      'poi_name' => $name,
      'country_code' => $country_code,
    ) );

    $poi = Doctrine::getTable( 'Poi' )->findOneById( 1 );

    $this->assertEquals( $name, $poi[ 'poi_name' ] );
    $this->assertEquals( $country_code, $poi[ 'country_code' ] );
  }
}