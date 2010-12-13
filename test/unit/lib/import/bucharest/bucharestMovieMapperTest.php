<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * test for Bucharest Movie mapper
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class bucharestMovieMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $params;

    public function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'bucharest' );
        $this->params = array( 'type' => 'movie', 'curl' => array( 'classname' => 'CurlMock', 'src' => TO_TEST_DATA_PATH . '/bucharest/movies.xml' ) );

        // Run import
        $importer = new Importer;
        $importer->addDataMapper( new bucharestMovieMapper( $this->vendor, $this->params ) );
        $importer->run();

    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapMovies()
    {
        $this->assertEquals( 7 , Doctrine::getTable( 'Movie' )->count() );
        $movies = Doctrine::getTable( 'Movie' )->findAll();
        
        $movie = $movies[0];
        $this->assertEquals( '7727770', $movie->vendor_movie_id );
        $this->assertEquals( 'Cronică de film: Scott Pilgrim împotriva tuturor', $movie->name );
        $this->assertStringStartsWith( 'Scott Pilgrim (Michael Cera) este la 22 de ani', $movie->plot );
        $this->assertEquals( $this->_getReviewFor1(), $movie->review );
        $this->assertEquals( 2 , $movie->rating );
        $this->assertEquals( null , $movie->director );
        $this->assertEquals( 'Michael Cera, Johnny Simmons, Alison Pill' , $movie->cast );

        // genre
        $this->assertEquals( 3 , $movie['MovieGenres']->count() );

        $this->assertEquals( 'Actiune' , $movie['MovieGenres']['Actiune']['genre']);
        $this->assertTrue( $movie['MovieGenres']['Actiune']->exists() );
        
        $this->assertEquals( 'Aventuri' , $movie['MovieGenres']['Aventuri']['genre']);
        $this->assertTrue( $movie['MovieGenres']['Aventuri']->exists() );

        $this->assertEquals( 'Comedie' , $movie['MovieGenres']['Comedie']['genre']);
        $this->assertTrue( $movie['MovieGenres']['Comedie']->exists() );

        // Media
        $this->assertEquals( 1 , $movie['MovieMedia']->count() );
        $this->assertEquals( 'http://storage0.dms.mpinteractiv.ro/media/401/401/6347/7727770/1/film-scottpilgrim.jpg' , $movie['MovieMedia'][0]['url'] );

        // Test another Movie
        $movie = $movies[4];
        $this->assertEquals( '7727404', $movie->vendor_movie_id );
        $this->assertEquals( 'Guillermo del Toro  renunţă la regia filmului "The Hobbit"', $movie->name );
        $this->assertStringStartsWith( 'După doi ani in care a lucrat intens la adaptarea cinematografică a romanului lui J.R.R. Tolkien,', $movie->plot );
        $this->assertEquals( 'După doi ani in care a lucrat intens la adaptarea cinematografică a romanului lui J.R.R. Tolkien, regizorul mexican Guillermo del Toro a anunţat că nu va mai regiza filmul "The Hobbit".', $movie->review );
        $this->assertEquals( null, $movie->cast);
        // genre
        $this->assertEquals( 0 , $movie['MovieGenres']->count() );

        // Media
        $this->assertEquals( 1 , $movie['MovieMedia']->count() );
        $this->assertEquals( 'http://storage0.dms.mpinteractiv.ro/media/401/401/6347/7727404/1/guilermo.jpg' , $movie['MovieMedia'][0]['url'] );

    }

    
    private function _getReviewFor1()
    {
        return <<<EOF
<p>Singurul obstacol în calea fericirii sunt cei şapte foşti
prieteni malefici ai Ramonei care vor să îl ucidă. Până să o
întâlnească pe ea Scott Pilgrim nu a fost nevoit să lupte pentru a
câştiga inima unei fete. Despărţirile erau de fapt cele mai
dificile. Fosta sa prietenă l-a părăsit şi a devenit o cântăreaţă
de succes. După această relaţie nefericită, Scott a decis să îşi
încerce norocul cu Knives, o colegă de liceu. După ce se
îndrăgosteşte iremediabil de Ramona, el încearcă să se despartă de
Knives, însă acest lucru se dovedeşte un proces mai dificil dect se
aştepta. Problema e că în cazul Ramonei, Scott trebuie să câştige
nu una, ci şapte lupte, cu foştii ei prieteni, printre care un star
rock, un vegetarian şi o pereche de gemeni identici. Pentru ca
povestea sa să aibă un final fericit el trebuie să îşi învingă toţi
adversarii.</p>
<p> </p>
EOF;
    }
}