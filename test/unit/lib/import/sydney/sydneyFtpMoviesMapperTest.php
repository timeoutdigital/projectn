<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/FTPClient.mock.php';

/**
 * Test class for sydney venues import
 *
 * @package test
 * @subpackage sydney.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 *
 * @version 1.0.1
 */
class sydneyFtpMoviesMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $params;
  
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('sydney');
    $this->params = array( 'type' => 'movie', 'ftp' => array(
                                                        'classname' => 'FTPClientMock',
                                                        'username' => 'test',
                                                        'password' => 'test',
                                                        'src' => '',
                                                        'dir' => '/',
                                                        'file' => TO_TEST_DATA_PATH . '/sydney/sydney_sample_films.xml'
                                                        )
        );

    // Run Import
    $importer = new Importer();
    $importer->addDataMapper( new sydneyFtpMoviesMapper( $this->vendor, $this->params ) );
    $importer->run();
        
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {

    $this->assertEquals( 1,
                         Doctrine::getTable( 'Movie' )->count(),
                        'Database should have same number of Movies as feed after import'
                         );
  }

  public function testHasImages()
  {
     $movies = Doctrine::getTable( 'Movie' )->findAll();

     $this->assertEquals( 'http://www.timeoutsydney.com.au/pics/venue/agnsw.jpg',
                          $movies[0]['MovieMedia'][0]['url']
                          );
  }


}
