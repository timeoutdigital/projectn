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
class LondonImport
{
  /**
   * @var array
   */
  private $venues = null;

  /**
   * Get all available Venues
   *
   * @return array All available Venues
   */
  public function loadFromSource()
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
              GROUP_CONCAT(
                DISTINCT category.name
                SEPARATOR ", "
              ) as vendor_category_names,
              category.id as category_ids,
              venue.id as vendor_poi_id,
              venue.opening_times,
              "London" as city,
              "GBR" as country,
              "en-GB" as language
            FROM
              venue,
              venue_category_mapping as map,
              category
            WHERE
              venue.id = map.venue_id
              AND
              map.category_id = category.id
            GROUP BY
              venue.id
            LIMIT
              20
            ;';
    
    $statement = $this->getSourceConnection()->prepare( $sql );

    if( $statement->execute() )
    {
      $results = $statement->fetchAll();
    }

    $this->venues = $results;
  }

  /**
   * Return the imported results
   */
  public function getData()
  {
    return $this->venues;
  }

  /**
   * set loaded data
   */
  public function bindData()
  {
    if( !is_array( $this->getData() ) )
    {
      throw new ImportException( 'Data must be loaded before being set.' );
    }

    foreach( $this->getData() as $venue )
    {
      $poi = new Poi();
      $poi->fromArray( $venue );
    }

    return true;
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

  private function getStoreConnection()
  {
    return Doctrine_Manager::connection( 'dev' );
  }

}
?>