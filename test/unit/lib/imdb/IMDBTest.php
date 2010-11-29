<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

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
  public function testFindMovieByTitleOnATitlePageWithAka()
  {
      $this->markTestIncomplete( 'IMDB changed their layout and require updating patten matching in our Class' );
    $movie = IMDB::findMovieByTitle( 'Memories of Underdevelopment' );
    $this->assertTrue( $movie instanceof IMDBMovie, 'Searching for Amelia should return a result' );
    $this->assertEquals( 'tt0063291', $movie->getId() );
  }

  public function testFindMovieByTitleOnAResultsPageWithPopularTitles()
  {
    $movie = IMDB::findMovieByTitle( 'Avatar' );
    $this->assertTrue( $movie instanceof IMDBMovie, 'Searching for Avatar should return a result' );
    $this->assertEquals( 'tt0499549', $movie->getId() );
  }

  public function testFindMovieByTitleOnAResultsPageWhereTitleIsNotInPopularTitles()
  {
    $movie = IMDB::findMovieByTitle( 'Amelia' );
    $this->assertTrue( $movie instanceof IMDBMovie, 'Searching for Amelia should return a result' );
    $this->assertEquals( 'tt1129445', $movie->getId() );
  }

  public function testFindMovieByTitleOnATitlePage()
  {
    $movie = IMDB::findMovieByTitle( 'Planet 51' );
    $this->assertTrue( $movie instanceof IMDBMovie, 'Searching for Planet 51 should return a result' );
    $this->assertEquals( 'tt0762125', $movie->getId() );
  }

  public function testFindMovieByTitleReturnsNullIfNoResults()
  {
    $movie = IMDB::findMovieByTitle( 'ljsdlkfsuroep' );
    $this->assertNull( $movie );
  }
}
?>
