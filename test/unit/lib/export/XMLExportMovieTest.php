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
    $vendor->save();
    $this->vendor = $vendor;

    $poiCat = new PoiCategory();
    $poiCat->setName( 'test' );
    $poiCat->save();

    $poi = new Poi();
    $poi->setPoiName( 'test name' );
    $poi->setStreet( 'test street' );
    $poi->setHouseNo('12' );
    $poi->setZips('1234' );
    $poi->setCity( 'test town' );
    $poi->setDistrict( 'test district' );
    $poi->setCountry( 'GRB' );
    $poi->setVendorPoiId( '123' );
    $poi->setLocalLanguage('en');
    $poi->setLongitude( '0.1' );
    $poi->setLatitude( '0.2' );
    $poi->link( 'Vendor', array( 1 ) );
    $poi->link('PoiCategories', array( 1 ) );
    $poi->save();

    $poi2 = new Poi();
    $poi2->setPoiName( 'test name' );
    $poi2->setStreet( 'test street' );
    $poi2->setHouseNo('12' );
    $poi2->setZips('1234' );
    $poi2->setCity( 'test town' );
    $poi2->setDistrict( 'test district' );
    $poi2->setCountry( 'GRB' );
    $poi2->setVendorPoiId( '123' );
    $poi2->setLocalLanguage('en');
    $poi2->setLongitude( '0.1' );
    $poi2->setLatitude( '0.2' );
    $poi2->link( 'Vendor', array( 1 ) );
    $poi2->link('PoiCategories', array( 1 ) );
    $poi2->save();

    $genre = new MovieGenre();
    $genre['genre'] = 'comedy';
    $genre->save();

    $genre = new MovieGenre();
    $genre['genre'] = 'horror';
    $genre->save();

    $movie = new Movie();
    $movie[ 'vendor_movie_id' ] = 1111;
    $movie[ 'Vendor' ] = $vendor;
    $movie[ 'Poi' ] = $poi;
    $movie[ 'name' ] = 'test movie name';
    $movie[ 'plot' ] = 'test movie plot';
    $movie[ 'review' ] = 'test movie review';
    $movie[ 'url' ] = 'http://movies.co.uk';
    $movie[ 'rating' ] = '1.1';
    $movie[ 'age_rating' ] = 'oap';
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
    $movie2[ 'Poi' ] = $poi2;
    $movie2[ 'name' ] = 'test movie name';
    $movie2[ 'plot' ] = 'test movie plot';
    $movie2[ 'review' ] = 'test movie review';
    $movie2[ 'url' ] = 'http://movies.co.uk';
    $movie2[ 'rating' ] = '1.2';
    $movie2[ 'age_rating' ] = 'oap';
    $movie2[ 'utf_offset' ] = '-01:00:00';
    $movie2->link( 'MovieGenres', array( 1, 2 ) );
    $movie2->save();

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

    $this->assertEquals( $this->vendor->getName(), $rootElement->getAttribute('vendor') );
    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $rootElement->getAttribute('modified') );

    $movieElement = $this->xpath->query( '/vendor-movies/movie' )->item(0);

    //movie@attributes
    $this->assertEquals( 'XXX000000000000000000000000000001', $movieElement->getAttribute( 'id' ) );
    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $movieElement->getAttribute( 'modified' ) );

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

    //movie/version/plot
    $this->assertEquals( 'test movie plot', $versionElement->getElementsByTagName( 'plot' )->item(0)->nodeValue );

    //movie/version/review
    $this->assertEquals( 'test movie review', $versionElement->getElementsByTagName( 'review' )->item(0)->nodeValue );

    //movie/version/url
    $this->assertEquals( 'http://movies.co.uk', $versionElement->getElementsByTagName( 'url' )->item(0)->nodeValue );

    //movie/version/rating
    $this->assertEquals( '1.1', $versionElement->getElementsByTagName( 'rating' )->item(0)->nodeValue );

    //movie/showtimes
    $showtimesElement = $movieElement->getElementsByTagName( 'showtimes' )->item(0);

    //movie/showtimes/place
    $place = $showtimesElement->getElementsByTagName( 'place' )->item(0);
    $this->assertEquals( '1', $place->getAttribute( 'place-id' ) );

    //test the second movie as well
    $placeId = $this->xpath->query( '/vendor-movies/movie[2]/showtimes/place' )->item(0);
    $this->assertEquals('2', $placeId->getAttribute( 'place-id' ) );

    //movie/showtimes/place
    $this->assertEquals( '', $place->getElementsByTagName( 'age_rating' )->item(0)->nodeValue );
  }

  /**
   * check properties tags
   */
  public function testPropertyTags()
  {
    //$properties = $this->domDocument->movie[0]->version->property;
    $propertyElements = $this->xpath->query( '/vendor-movies/movie[1]/version/property' );
    $this->assertEquals( 'age_rating', $propertyElements->item(0)->getAttribute( 'key' ) );
    $this->assertEquals( 'oap', $propertyElements->item(0)->nodeValue );
    //$this->assertEquals( 'movie key 1', $propertyElements->item(1)->getAttribute( 'key' ) );
    //$this->assertEquals( 'movie value 1', $propertyElements->item(1)->nodeValue );
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
}
?>
