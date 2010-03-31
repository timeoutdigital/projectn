<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../bootstrap.php';

/**
 * Test class for XMLExportMovie.
 * Generated by PHPUnit on 2010-01-20 at 12:10:23.
 */
class XMLExportMovieTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var XMLExportMovie
   */
  protected $object;

  protected $specialChars = '&<>\'"';

  protected $escapedSpecialChars;

  /*
   * @var DOMDocument
   */
  protected $domDocument;

  /*
   * @var DOMXPath
   */
  protected $xpath;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = new Vendor();
    $vendor['city'] = 'test';
    $vendor['language'] = 'en-GB';
    $vendor['time_zone'] = 'Europe/London';
    $vendor['inernational_dial_code'] = '+44';
    $vendor['airport_code'] = 'XXX';
    $vendor['country_code'] = 'XX';
    $vendor->save();
    $this->vendor = $vendor;

    $genre = new MovieGenre();
    $genre['genre'] = 'comedy';
    $genre->save();

    $genre = new MovieGenre();
    $genre['genre'] = 'horror';
    $genre->save();

    $movie = new Movie();
    $movie[ 'vendor_movie_id' ] = 1111;
    $movie[ 'Vendor' ] = $vendor;
    $movie[ 'name' ] = 'test movie name';
    $movie[ 'plot' ] = 'test movie plot';
    $movie[ 'review' ] = 'test movie review';
    $movie[ 'url' ] = 'http://movies.co.uk';
    $movie[ 'rating' ] = '1.1';
    $movie[ 'tag_line' ] = 'test movie tag-line';
    $movie[ 'director' ] = 'test director';
    $movie[ 'writer' ] = 'test writer';
    $movie[ 'cast' ] = 'test cast';
    $movie[ 'age_rating' ] = 'test age-rating';
    $movie[ 'release_date' ] = '2010-02-28';
    $movie[ 'duration' ] = 'test duratione';
    //$movie[ 'country' ] = 'test country';
    $movie[ 'language' ] = 'test language';
    $movie[ 'aspect_ratio' ] = 'test aspect-ratio';
    $movie[ 'sound_mix' ] = 'test sound-mix';
    $movie[ 'company' ] = 'test company';
    $movie[ 'utf_offset' ] = '-01:00:00';
    $movie->link( 'MovieGenres', array( 1, 2 ) );
    $movie->save();

    $property = new MovieProperty();
    $property[ 'lookup' ] = 'movie key 1';
    $property[ 'value' ] = 'movie value 1';
    $property->link( 'Movie', array( 1 ) );
    $property->save();

    $property2 = new MovieProperty();
    $property2[ 'lookup' ] = 'movie key 2';
    $property2[ 'value' ] = 'movie value 2';
    $property2->link( 'Movie', array( 1 ) );
    $property2->save();

    $property = new MovieMedia();
    $property[ 'ident' ] = 'md5 hash of the url';
    $property[ 'mime_type' ] = 'image/';
    $property[ 'url' ] = 'url';
    $property->link( 'Movie', array( 1 ) );
    $property->save();

    $movie2 = new Movie();
    $movie2[ 'vendor_movie_id' ] = 1111;
    $movie2[ 'Vendor' ] = $vendor;
    $movie2[ 'name' ] = 'test movie name';
    $movie2[ 'plot' ] = 'test movie plot';
    $movie2[ 'review' ] = 'test movie review';
    $movie2[ 'url' ] = 'http://movies.co.uk';
    $movie2[ 'rating' ] = '0.0';
    $movie2[ 'tag_line' ] = 'test movie tag-line';
    $movie2[ 'director' ] = 'test director';
    $movie2[ 'writer' ] = 'test writer';
    $movie2[ 'cast' ] = 'test cast';
    $movie2[ 'age_rating' ] = 'test age-rating';
    $movie2[ 'release_date' ] = '2010-02-28';
    $movie2[ 'duration' ] = 'test duratione';
    //$movie2[ 'country' ] = 'test country';
    $movie2[ 'language' ] = 'test language';
    $movie2[ 'aspect_ratio' ] = 'test aspect-ratio';
    $movie2[ 'sound_mix' ] = 'test sound-mix';
    $movie2[ 'company' ] = 'test company';
    $movie2[ 'utf_offset' ] = '-01:00:00';
    $movie2->link( 'MovieGenres', array( 1, 2 ) );
    $movie2->save();

    $movie3 = new Movie();
    $movie3[ 'vendor_movie_id' ] = 1111;
    $movie3[ 'Vendor' ] = $vendor;
    $movie3[ 'name' ] = 'test movie name';
    $movie3[ 'plot' ] = 'test movie plot';
    $movie3[ 'review' ] = 'test movie review';
    $movie3[ 'utf_offset' ] = '-01:00:00';
    $movie3->link( 'MovieGenres', array( 1, 2 ) );
    $movie3->save();

    $this->destination = dirname( __FILE__ ) . '/../../export/movie/test.xml';
    $this->export = new XMLExportMovie( $this->vendor, $this->destination );

    $this->export->run();
    $this->domDocument = new DOMDocument();
    $this->domDocument->load( $this->destination );
    $this->xpath = new DOMXPath($this->domDocument);

    $this->escapedSpecialChars = htmlspecialchars( $this->specialChars );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Test movie tag has correct attributes and children
   */
  public function testMovieTag()
  {
    $rootElement = $this->domDocument->firstChild;
    $this->assertEquals('vendor-movies', $rootElement->nodeName);

    $this->assertEquals( XMLExport::VENDOR_NAME, $rootElement->getAttribute('vendor') );
    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $rootElement->getAttribute('modified') );

    $movieElement = $this->xpath->query( '/vendor-movies/movie' )->item(0);

    //movie@attributes
    $this->assertEquals( 'XXX000000000000000000000000000001', $movieElement->getAttribute( 'id' ) );
    //$this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $movieElement->getAttribute( 'modified' ) );

    //movie/name
    $this->assertEquals( 'test movie name', $movieElement->getElementsByTagName( 'name' )->item(0)->nodeValue );

    $versionElement = $movieElement->getElementsByTagName( 'version' )->item(0);//$this->domDocument->movie[0]->version;

    //movie/version
    $this->assertEquals( 'en', $versionElement->getAttribute( 'lang' ) );

    //movie/version/name
    $this->assertEquals( 'test movie name', $versionElement->getElementsByTagName( 'name' )->item(0)->nodeValue );//(string) $versionTag->name );

    //movie/version/genre
    $genreElements = $versionElement->getElementsByTagName( 'genre' );
    $this->assertEquals( 'comedy, horror', $genreElements->item(0)->nodeValue );

    //movie/version/tag-line
    $this->assertEquals( 'test movie tag-line', $versionElement->getElementsByTagName( 'tag-line' )->item(0)->nodeValue );

    //movie/version/plot
    $this->assertEquals( 'test movie plot', $versionElement->getElementsByTagName( 'plot' )->item(0)->nodeValue );

    //movie/version/review
    $this->assertEquals( 'test movie review', $versionElement->getElementsByTagName( 'review' )->item(0)->nodeValue );

    //movie/version/rating
    $this->assertEquals( '1.1', $versionElement->getElementsByTagName( 'rating' )->item(0)->nodeValue );

    //movie/version/director
    $this->assertEquals( 'test director', $versionElement->getElementsByTagName( 'director' )->item(0)->nodeValue );

    //movie/version/writer
    $this->assertEquals( 'test writer', $versionElement->getElementsByTagName( 'writer' )->item(0)->nodeValue );

    //movie/version/cast/actor/actor-name
    $actorElement = $this->xpath->query( '/vendor-movies/movie/version/cast/actor' )->item(0);

    $this->assertEquals( 'test cast', $actorElement->getElementsByTagName( 'actor-name' )->item(0)->nodeValue );

    $additionalDetailsElement = $movieElement->getElementsByTagName( 'additional-details' )->item(0);//$this->domDocument->movie[0]->additional-details;

    //movie/additional-details/website
    $this->assertEquals( 'http://movies.co.uk', $additionalDetailsElement->getElementsByTagName( 'website' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test age-rating', $additionalDetailsElement->getElementsByTagName( 'age-rating' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( '2010-02-28', $additionalDetailsElement->getElementsByTagName( 'release-date' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test duratione', $additionalDetailsElement->getElementsByTagName( 'duration' )->item(0)->nodeValue );

    //movie/additional-details/website
    //$this->assertEquals( 'country', $additionalDetailsElement->getElementsByTagName( 'country' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test language', $additionalDetailsElement->getElementsByTagName( 'language' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test aspect-ratio', $additionalDetailsElement->getElementsByTagName( 'aspect-ratio' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test sound-mix', $additionalDetailsElement->getElementsByTagName( 'sound-mix' )->item(0)->nodeValue );

    //movie/additional-details/website
    $this->assertEquals( 'test company', $additionalDetailsElement->getElementsByTagName( 'company' )->item(0)->nodeValue );
  }


  /**
   * check if additional-details node is not appended if no children are present
   */
  public function testNoAdditionalDetailsTagIfNoChildren()
  {
    $additionalDetailsElement = $this->xpath->query( '/vendor-movies/movie[1]/additional-details' )->item(0);
    $this->assertTrue( $additionalDetailsElement instanceof DomElement  );

    $additionalDetailsElement = $this->xpath->query( '/vendor-movies/movie[3]/additional-details' )->item(0);
    $this->assertFalse( $additionalDetailsElement instanceof DomElement  );
  }

  /**
   * check properties tags
   */
  public function testPropertyTags()
  {
    //$properties = $this->domDocument->movie[0]->version->property;
    $propertyElements = $this->xpath->query( '/vendor-movies/movie[1]/version/property' );
    $this->assertEquals( 'movie key 1', $propertyElements->item(0)->getAttribute( 'key' ) );
    $this->assertEquals( 'movie value 1', $propertyElements->item(0)->nodeValue );
    $this->assertEquals( 'movie key 2', $propertyElements->item(1)->getAttribute( 'key' ) );
    $this->assertEquals( 'movie value 2', $propertyElements->item(1)->nodeValue );
  }

    /**
     * check properties tags
     */
    public function testMediaTags()
    {
      $this->markTestSkipped('temporarily removed media tags');
      $propertyElements = $this->xpath->query( '/vendor-movies/movie[1]/version/media' );
      $this->assertEquals( 'image/', $propertyElements->item(0)->getAttribute('mime-type') );
      $this->assertEquals( 'url', $propertyElements->item(0)->nodeValue );
    }

    public function testRatingRangeIsOneToFiveInclusive()
    {
      $movies = $this->xpath->query( '//movie' );
      $this->assertEquals( 3, $movies->length );

      $movie1 = $this->xpath->query( '//movie[1]' )->item(0);
      $this->assertEquals(1, $movie1->getElementsByTagName( 'rating' )->length );

      $movie2 = $this->xpath->query( '//movie[2]' )->item(0);
      $this->assertEquals(0, $movie2->getElementsByTagName( 'rating' )->length );

      $movie3 = $this->xpath->query( '//movie[3]' )->item(0);
      $this->assertEquals(0, $movie3->getElementsByTagName( 'rating' )->length );
    }
}


?>
