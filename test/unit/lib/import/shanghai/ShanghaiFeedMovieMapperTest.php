<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of Shanghai Movie Mapper
 *
 * @package test
 * @subpackage shanghai.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class ShanghaiFeedMovieMapperTest extends PHPUnit_Framework_TestCase
{
   /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    Doctrine::loadData('data/fixtures');

    $params = array( 'datasource' => array( 'classname' => 'CurlMock', 'url' => TO_TEST_DATA_PATH . '/shanghai/MoivesIsOnView.xml' ) );
    $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'shanghai' );
    
    $importer = new Importer( );
    $importer->addDataMapper( new ShanghaiFeedMovieMapper($vendor, $params) );
    $importer->run();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMovieMapper()
  {
      $movies = Doctrine::getTable( 'Movie' )->findAll();

      $this->assertEquals( 4, $movies->count(), 'There are 4 Movies in the Feed');

      $movie = $movies[2];
      $this->assertEquals( '4', $movie['vendor_movie_id'] );
      $this->assertEquals( '精武风云·陈真', $movie['name'] );
      $this->assertStringStartsWith( '陈真(甄子丹饰)当年为报杀师之仇，独闯虹口道场', $movie['plot'] );
      $this->assertStringStartsWith( '<p style="text-align: center;"><img', $movie['review'] ); // We are not cleaning HTML tags in import, Export will clean this
      $this->assertEquals( '刘伟强 Wai Keung Lau', $movie['director'] );
      $this->assertEquals( null, $movie['writer'] );
      $this->assertEquals( '3', $movie['rating'] );
      $this->assertEquals( '甄子丹 Donnie Yen, 舒淇 Qi Shu, 黄秋生 Anthony Wong Chau-Sang, 仓田保昭 Yasuaki Kurata, 余文乐 Shawn Yue, Karl Ignaczak, Ryu Kohata, 黄渤 Huang Bo, 陈佳佳 Jiajia Chen, 霍思燕 Siyan Kuo, 周扬 Zhou Yang, Shi Feng', $movie['cast'] );

      $this->assertEquals( 3, $movie['MovieGenres']->count() );
      $this->assertEquals( '动作', $movie['MovieGenres'][0]['genre'] );
      
      $movie = $movies[0];
      $this->assertEquals( '2', $movie['vendor_movie_id'] );
      $this->assertEquals( '狄仁杰之通天帝国', $movie['name'] );
      $this->assertContains( '公元690年，经过八年苦心经营，武则天即将正式登基，成为中国历史上第一位女皇帝', $movie['plot'] );
      $this->assertContains( '威尼斯电影节上，徐克为吴宇森', $movie['review'] );
      $this->assertEquals( '徐克 Hark Tsui', $movie['director'] );
      $this->assertEquals( null, $movie['writer'] );
      $this->assertEquals( '4', $movie['rating'] );
      $this->assertEquals( '刘德华 Andy Lau, 梁家辉 Tony Leung Ka Fai, 李冰冰 Bingbing Li, 刘嘉玲 Carina Lau, Jean-Michel Casanova, 姚橹 Lu Yao', $movie['cast'] );

      $this->assertEquals( 3, $movie['MovieGenres']->count() );
  }
}

?>
