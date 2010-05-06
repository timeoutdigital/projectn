<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for sydney venues import
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 *
 * @version 1.0.1
 */
class sydneyFtpMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $feed     = simplexml_load_file( TO_TEST_DATA_PATH . '/sydney_sample_films.xml' );
    $vendor   = ProjectN_Test_Unit_Factory::add( 'Vendor',  array( 
                                                 'city'     => 'sydney', 
                                                 'language' => 'en-AU', 
                                                 'country'  => 'AUS', 
                                                 ) );

    $importer = new Importer();
    $importer->addDataMapper( new sydneyFtpMoviesMapper( $vendor, $feed ) );
    $importer->run();

    $this->assertEquals( 1,
                         Doctrine::getTable( 'Movie' )->count(),
                        'Database should have same number of Movies as feed after import'
                         );
  }
}
