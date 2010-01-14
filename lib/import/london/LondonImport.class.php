<?php

/**
 * Imports data from London database
 *
 * @package projectn.lib.import.london
 *
 * @author clarence <clarencelee@timeout.com>
 */
class LondonImport
{
  /**
   * @var array
   */
  private $venues = null;

  /**
   * @var array
   */
  private $_validationErrors = array();

  /**
   * Get all available Venues
   *
   * @return array All available Venues
   */
  public function loadFromSource( $limit = 184, $offset = 0 )
  {
    if( !is_int( $limit ) || !is_int( $offset ) )
    {
      $message = '';//'loadFromSource( $limit, $offset ), $limit must be an integer. Got (' . $limit . ')';
      throw new LondonImportException( $message );
    }

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
              "GB" as country_code,
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
              :limit
            OFFSET
              :offset
            ;';
    
    $statement = $this->getLondonPdo()->prepare( $sql );
    $statement->bindParam( 'limit', $limit, PDO::PARAM_INT );
    $statement->bindParam( 'offset', $offset, PDO::PARAM_INT );
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
   * validate and save loaded data
   */
  public function save()
  { 
    if( !is_array( $this->getData() ) )
    {
      throw new ImportException( 'Data must be loaded before being set.' );
    }

    //Switch back to local database connection and explicitly the table to validate
    Doctrine_Manager::connection( 'mysql://projectn:!ntcejorp!@localhost/projectn' );
    Doctrine::getTable( 'Poi' )->setAttribute( Doctrine::ATTR_VALIDATE, true );

    foreach( $this->getData() as $venue )
    {
      $success = false;

      $poi = new Poi();

      $vendor = Doctrine::getTable( 'Vendor' )->getVendorByCityAndLanguage( 'london', 'english' );
      $poi->link( 'Vendor' , array( $vendor[ 'id' ] ) );

      //TODO map this properly
      $category = Doctrine::getTable( 'PoiCategory' )->findOneById( 1 );
      $poi->link( 'PoiCategory', array( $category[ 'id' ] ) );

      $poi[ 'street' ] = $venue[ 'street' ];
      $poi[ 'city' ] = $venue[ 'city' ];
      $poi[ 'country_code' ] = $venue[ 'country_code' ];
      $poi[ 'longitude' ] = $venue[ 'longitude' ];
      $poi[ 'latitude' ] = $venue[ 'latitude' ];
      
      if( $poi->isValid() )
      {
        $poi->save();
        $success = true;
      }
      else
      {
        var_dump( $poi->getErrorStackAsString() );
        array_push( $this->_validationErrors, $poi->getErrorStack() );
        $success = false;
      }

      $poi->free( true );
      return $success;
    }
  }

  /**
   * Returns validation errors from save
   *
   * @return array
   */
  public function getValidationErrors()
  {
    return $this->_validationErrors;
  }

  /**
   * Get connection handler for the London Datadatabase
   *
   * @return PDO Connection handler
   */
  private function getLondonPdo()
  {
    $doctrineConnection = Doctrine_Manager::connection( 'mysql://timeout:65dali32@192.9.215.250/searchlight', 'london' );
    return $doctrineConnection->getDbh();
  }

  private function getStoreConnection()
  {
    return Doctrine_Manager::connection( 'dev' );
  }

}
?>