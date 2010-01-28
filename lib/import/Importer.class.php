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
  private $importData = array();

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
   * Adds an ImportData to be saved
   */
  public function addImportData( ImportData $importData )
  {    
    $this->importData[] = $importData;
  }

  /**
   * gets all added ImportData
   */
  public function getImportData()
  {
    return $this->importData;
  }

  public function run()
  {
  }
}
