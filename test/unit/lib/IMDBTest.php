<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the IMDB class 
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author clarence lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class IMDBTest extends PHPUnit_Framework_TestCase
{

  /**
   * @todo Implement testGetResponse().
   */
  public function testGetFilm()
  {
    $movie = 'Avatar';
    $imdb = new IMDB( $movie );
    $this->assertEquals( $movie, $imdb->getTitle() );
  }

}
?>
