<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Movie Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class MovieTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Movie
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $movieObj = new Movie();
    $movieObj[ 'vendor_id' ] = 1;
    $movieObj[ 'vendor_movie_id' ] = 1;
    $movieObj[ 'name' ] = 'Test Movie';
    $movieObj[ 'utf_offset' ] = '0.00';
    $movieObj->save();

    $this->object = Doctrine::getTable('Movie')->findOneById( $movieObj['id'] );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * test if the add property adds the properties
   */
  public function testAddProperty()
  {
    $this->object->addProperty( 'test prop lookup', 'test prop value' );
    $this->object->addProperty( 'test prop lookup 2', 'test prop value 2' );
    $this->object->save();

    $this->object = Doctrine::getTable('Movie')->findOneById( $this->object['id'] );

    $this->assertEquals( 'test prop lookup', $this->object[ 'MovieProperty' ][ 0 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value', $this->object[ 'MovieProperty' ][ 0 ][ 'value' ] );

    $this->assertEquals( 'test prop lookup 2', $this->object[ 'MovieProperty' ][ 1 ][ 'lookup' ] );
    $this->assertEquals( 'test prop value 2', $this->object[ 'MovieProperty' ][ 1 ][ 'value' ] );
  }

  /*
   * test if the genres are added (w/a duplicates)
   */
  public function testAddGenres()
  {
    $this->object->addGenre( 'test' );
    $this->object->addGenre( 'test2' );
    $this->object->addGenre( 'test' );
    $this->object->save();

    $this->assertEquals( 2, Doctrine::getTable('MovieGenre')->findAll()->count() );
  }
}
?>
