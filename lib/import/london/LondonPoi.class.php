<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of
 *
 * @author clarence
 */
class LondonPoi
{

  /**
   * Get all available Venues
   *
   * @return array All available Venues
   */
  public function getAllFromSource()
  {
    $results = false;

    $sql = '
            SELECT
              venue.name as poi_name,
              venue.name as alternative_name,
              SUBSTR( venue.address, ",", 1 ) as street,
              venue.travel as public_transport,
              venue.latitude,
              venue.longitude,
              category.name as vendor_category,
              venue.id as vendor_poi_id,
              venue.opening_times,
              "London" as city,
              "GBR" as country,
              "en-GB" as language
            FROM
              venue
            LEFT JOIN
              venue_category_mapping as map
              ON
              venue.id = map.venue_id
            LEFT JOIN
              category
              ON
              map.category_id = category.id
            GROUP BY
              venue.id
            LIMIT
              2
            ;';
    $statement = $this->getSourceConnection()->prepare( $sql );

    if( $statement->execute() )
    {
      $results = $statement->fetchAll();
    }
    return $results;
  }

  /**
   * Insert source results into store
   */
  public function insertData()
  {
    $sql = '
            INSERT INTO
              poi (
                vendor_id,
                poi_category_id,
                poi_name,
                public_transport_links,
                latitude,
                longitude,
                vendor_category,
                vendor_poi_id,
                openingtimes,
                language
              )
            VALUES (
              1,
              1,
              :name,
              :public_transport_links,
              :latitude,
              :longitude,
              :vendor_category,
              :vendor_poi_id,
              :opening_times,
              :language
              )
            ';
//            #city,
//            #country,
//            #language
//            #street
    $statement = $this->getStoreConnection()->prepare( $sql );

    $venues = $this->getAllFromSource();

    foreach ( $venues as $venue )
    {
      $statement->execute( array(
        'name'   => $venue[ 'name' ],
        'public_transport_links' => $venue[ 'public_transport' ],
        'latitude' => $venue[ 'latitude' ],
        'longitude' => $venue[ 'longitude' ],
        'vendor_category' => $venue[ 'vendor_category' ],
        'vendor_poi_id' => $venue[ 'vendor_poi_id' ],
        'opening_times' => $venue[ 'opening_times' ],
        'language' => $venue[ 'language' ],
      ) );
    }
  }

  /**
   * Get all Venues from store database
   *
   * @return PDO All available Venues from store
   */
  public function getAllFromStore()
  {
    $results = false;

    $sql = '
            SELECT
              *
            FROM
              poi
            ;';

    $statement = $this->getStoreConnection()->prepare( $sql );

    if( $statement->execute() )
    {
      $results = $statement->fetchAll();
    }

    return $results;
  }

  /**
   * Get connection handler for the source database
   *
   * @return PDO Connection handler
   */
  private function getSourceConnection()
  {
    $doctrineConnection = Doctrine_Manager::connection( 'mysql://timeout:65dali32@192.9.215.250/searchlight', 'source' );
    return $doctrineConnection->getDbh();
  }

  /**
   * Get connection handler for the source database
   *
   * @return PDO Connection handler
   */
  private function getStoreConnection()
  {
    $doctrineConnection = Doctrine_Manager::connection( 'mysql://projectn:!ntcejorp!@localhost/projectn', 'store' );
    return $doctrineConnection->getDbh();
  }

}
?>
