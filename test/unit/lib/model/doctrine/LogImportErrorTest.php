<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for LogImportErrror Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class LogImportErrrorTest extends PHPUnit_Framework_TestCase
{  
  /**
   *
   * @var Doctrine_Record
   */
  private $LogImportError;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();
      Doctrine::loadData('data/fixtures');
      ImportLogger::getInstance()->enabled( true );
      ImportLogger::getInstance()->progressive( true );

      $poi = ProjectN_Test_Unit_Factory::add( 'poi' );
      $poi[ 'poi_name' ] = '';
      $poi[ 'street' ] = 'test LogImportErrror street';

      ImportLogger::getInstance()->addError( new Exception(), $poi, 'failed to save record' );

      ImportLogger::getInstance()->addError( new Exception(), NULL, 'failed to save record' );

      $poi2 = ProjectN_Test_Unit_Factory::add( 'poi' );
      $poi2[ 'poi_name' ] = '';
      $poi2[ 'street' ] = 'test LogImportErrror street';
      $poi2[ 'vendor_poi_id' ] = NULL;

      ImportLogger::getInstance()->addError( new Exception(), $poi2, 'failed to save record' );

      $movie = ProjectN_Test_Unit_Factory::add( 'PoiCategory' );

      ImportLogger::getInstance()->addError( new Exception(), $movie, 'failed to save record' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ImportLogger::getInstance()->unsetSingleton();
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetErrorObjectSuccess()
  {
      $LogImportError = Doctrine::getTable( 'LogImportError' )->find( 1 );

      $LogImportErrorObject = $LogImportError->getErrorObject();

      $this->assertEquals( '', $LogImportErrorObject[ 'poi_name' ] );
      $this->assertEquals( 'test LogImportErrror street', $LogImportErrorObject[ 'street' ] );
  }

  public function testGetErrorObjectFail()
  {
      $LogImportError = Doctrine::getTable( 'LogImportError' )->find( 2 );

      $LogImportErrorObject = $LogImportError->getErrorObject();

      $this->assertFalse( $LogImportErrorObject );
  }

  public function testGetIsFixable()
  {
      $LogImportErrors = Doctrine::getTable( 'LogImportError' )->findAll();

      $this->assertTrue( $LogImportErrors[0]->getIsFixable() );
      $this->assertFalse( $LogImportErrors[1]->getIsFixable() );
      $this->assertFalse( $LogImportErrors[2]->getIsFixable() );
      $this->assertFalse( $LogImportErrors[3]->getIsFixable() );
  }
}
