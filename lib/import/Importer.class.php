<?php
/**
 * @package projectn
 * @subpackage lib
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd &copyright; 2009
 *
 * @version 1.0.0
 *
 *
 */
class Importer
{
  /**
   * @var array
   */
  private $loggers = array();

  /**
   * @var array
   */
  private $dataMapper = array();

  /**
   * Adds a logger
   * 
   * @param logger $logger
   */
  public function registerLogger( logger $logger )
  {
    if( !isset( $this->loggers[ $logger->getType() ] ) )
    {
      $this->loggers[ $logger->getType() ] = array();
    }
    else if( in_array( $logger, $this->loggers[ $logger->getType() ] ) )
    {
      return;
    }

    $this->loggers[ $logger->getType() ][] = $logger;
  }

  /**
   * Retrieves all registered loggers
   */
  public function getLoggers()
  {
    return $this->loggers;
  }

  /**
   * Adds an DataMapper to be saved
   */
  public function addDataMapper( DataMapper $importData )
  {    
    $this->dataMapper[] = $importData;
  }

  /**
   * gets all added DataMapper
   */
  public function getDataMappers()
  {
    return $this->dataMapper;
  }

  public function run()
  {
    foreach( $this->getDataMappers() as $dataSource )
    {
      foreach( $dataSource->getMapMethods() as $mapMethod )
      {
         $mapMethod->invoke( $dataSource );
      }
    }
  }

  /**
   * Listens to DataMapper notifications
   * 
   * @param Doctrine_Record $record
   */
  public function onRecordMapped( Doctrine_Record $record )
  {
    //record exists?
    //transform( $records )
//    if( $record->isValid( true ) )
//    {
//      $record->save();
//    }
  }
}
