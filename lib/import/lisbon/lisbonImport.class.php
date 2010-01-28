<?php
/**
 * Imports data for Lisbon
 *
 * Lison Importer
 *
 * @package projectn
 * @subpackage lisbon.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class lisbonImport extends Importer
{
  /*
   * var logger
   */
  private $logger = array();

  public function __construct(  )
  {
  }

  /**
   * Set a logger for the import
   *
   * @param logger $logger
   */
  public function setLogger( logger $logger )
  {
    $this->loggers[ $logger->type ] = $logger;
  }

  /**
   * Return a logger by type (poi|event|movie)
   *
   * @param string $type
   * @returns logger
   */
  public function getLogger( $type )
  {
    $loggerTypeAvailable = count( array_keys( $this->loggers, $type ) );

    if( $loggerTypeAvailable )
    {
      return $this->loggers[ $type ];
    }
  }

  /**
   * Runs the import
   */
  public function run()
  {
    
  }

  /**
   * Receives a Doctrine_Record to validate and save
   *
   * @param Doctrine_Record $record
   */
  public function onMap( Doctrine_Record $record )
  {
    
  }
}


?>
