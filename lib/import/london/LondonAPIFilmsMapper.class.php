<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage london.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LondonAPIFilmsMapper extends LondonAPIBaseMapper
{

  /**
   * Map restaurant data to Movie and notify the Importer as each Movie is mapped
   */
  public function mapMovie()
  {
    $this->crawlApi();
  }

  /**
   * Returns the London API URL
   *
   * @return string
   */
  protected function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getFilm.xml';
  }

  /**
   * Returns the API type
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  protected function getApiType()
  {
    return 'Films';
  }

  /**
   * Map $movieXml into a Movie object and pass to Importer
   *
   * @param SimpleXMLElement $movieXml
   */
  protected function doMapping( SimpleXMLElement $movieXml )
  {
    $movie = new Movie();
    $movie['vendor_id']         = $this->vendor['id'];
    $movie['vendor_movie_id']   = (string) $movieXml->uid;
    $movie['name']              = (string) $movieXml->name;
    $movie['url']               = (string) $movieXml->webUrl;
    $movie['plot']              = (string) $movieXml->description;
    $movie['age_rating']        = (string) $movieXml->cert;

    //@todo resolve below field
    $movie['utf_offset']        = 0;

    $movie->addProperty( 'genre',    (string) $movieXml->genre );
    $movie->addProperty( 'release',  (string) $movieXml->released );
    $movie->addProperty( 'duration', (string) $movieXml->duration );
    $movie->addProperty( 'director', (string) $movieXml->director );

    foreach( $movieXml->cast->name as $castMember )
    {
      $movie->addProperty( 'castMember', (string) $castMember );
    }

    $this->notifyImporter( $movie );
    $movie->free(true);
  }
}