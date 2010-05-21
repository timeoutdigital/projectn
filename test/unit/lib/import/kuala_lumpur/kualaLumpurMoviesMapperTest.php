<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Events mapper
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
class kualaLumpurMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en',
      'inernational_dial_code' => '+60',
      ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_movies.xml' );
    $this->runImport();

    $this->movies = Doctrine::getTable( 'Movie' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $this->assertEquals( 1,
                         $this->movies->count(),
                         'check import count is 1. 5 events in the feed, 1 is a movie.'
                         );

    $this->assertEquals( '5158',
                         $this->movies[0]['vendor_movie_id'],
                         'Check id'
                         );

    $this->assertEquals( 'Deepak Menon Film Showing and Conversations',
                         $this->movies[0]['name'],
                         'Check name'
                         );

    $this->assertEquals( 'http://www.instantcafetheatre.com/',
                         $this->movies[0]['url'],
                         'Check url'
                         );
  }

  public function testImage()
  {
    $this->assertEquals( 'http://www.timeoutkl.com/uploadfiles/image/Events/Film/Big/bigimg_DeepakMenon_GravelRoad.jpg',
                          $this->movies[0]['MovieMedia'][0]['url'],
                          'Checking movie media'
                          );
  }

  public function testReview()
  {
    $review = <<<EOF
<p><em><img hspace="5" height="279" width="194" vspace="5" align="right" src="http://www.timeoutkl.com/uploadfiles/image/Events/Film/Big/img_DeepakMenon_ChemmanChaalai.jpg" alt="Chemman Chaalai" /></em>Come meet Deepak Menon, one of the most influential independent Tamil film director in Malaysia. Watch the screenings of his films. Afterward, have a chat with him, his mother Sooria Kumari, a retired Tamil school teacher and the co-writer of his film Chemman Chaalai and some of the cast most of whom were amateurs.<br />
<br />
<em>8.30pm, Saturday, May 7</em><br />
<strong>Chemman Chaalai (The Gravel Road)</strong><br />
- Chemman Chaalai is a drama set in the 1960s in a rubber estate in Malaysia. The story revolves around Shanta and her rubber tappers family living in the estate. Shantha aspires to leave the estate and further her studies. However, due to many unfortunate circumstances this dream becomes a difficult one to achieve.<br />
<br />
<em><img hspace="5" height="217" width="194" vspace="5" align="right" src="http://www.timeoutkl.com/uploadfiles/image/Events/Film/Big/img_DeepakMenon_Chalanggai.jpg" alt="Chemman Chaalai" /></em><em>Director's Note</em><br />
Malaysian Indians have little control on the economy and politics in Malaysia. A way forward would be education. The film emphases the importance of education for the growth of the community and nation.<br />
<em><br />
</em><em>8.30pm, Sunday, May 8</em><br />
<strong>Chalanggai (Dancing Bells)</strong><br />
- Uma lives with her mother, Muniammah, and elder brother Siva, in a soon to be demolished neighborhood secluded in the backyards of the rapidly developing Brickfields. Chalanggai is a beautifully realistic and detailed portrait of a family that has to survive and above all has to maintain its dignity. The film was shot entirely on location in Brickfield and the cast is made up of local amateurs.</p>
EOF;

    $this->assertEquals( $review,
                         $this->movies[0]['review'],
                         'Checking review'
                          );
  }

  private function runImport()
  {
    $importer = new Importer();
    $importer->addDataMapper( new kualaLumpurMoviesMapper( $this->vendor, $this->xml ) );
    //$importer->addLogger( new echoingLogger( ));
    $importer->run();
  }
}
