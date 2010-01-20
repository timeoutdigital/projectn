<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../lib/processXml.class.php';
require_once dirname(__FILE__).'/../../../../../lib/import/ny/processNyMoviesXml.php';

/**
 * Test class for processNyXml.
 * Generated by PHPUnit on 2010-01-14 at 13:10:31.
 */
class processNyXmlTest extends PHPUnit_Framework_TestCase {
  /**
   * @var processNyXml
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->object = new processNyMoviesXml(dirname(__FILE__).'/../../../data/tms.xml');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
  }

  /**
  * Test that the movies are loaded
  */
  public function testSettingAndGettingMovies()
  {
    $this->object->setMovies('/xffd/movies');
    $this->assertType('array', $this->object->getMovies());
  }


  

}
?>
