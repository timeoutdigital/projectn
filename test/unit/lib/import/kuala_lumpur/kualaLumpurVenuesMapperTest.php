<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Venues mapper
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
class kualaLumpurVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $importer = new Importer();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en'
      ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_venues.xml' );

    $importer->addDataMapper( new kualaLumpurVenuesMapper( $this->vendor, $this->xml ) );
    $importer->run();

    $this->pois = Doctrine::getTable( 'Poi' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $this->assertEquals( count( $this->xml->venueDetails ),
                         count( $this->pois ),
                         'Should have same number of Pois in db as in xml.');

    $this->assertEquals( $this->pois[0]['latitude'],
                         101.746359,
                         'Checking latitude'
                         );

    $this->assertEquals( $this->pois[0]['longitude'],
                         3.209707,
                         'Checking longitude'
                         );
  }
}
