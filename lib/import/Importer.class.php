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
   * @var boolean
   */
  private $printProgress = false;

  /**
   * @var array
   */
  private $loggers = array();

  /**
   * @var array
   */
  private $dataMapper = array();
  
  public function __construct( $printProgress = false )
  {
    $this->printProgress = $printProgress;
    $this->output( 'awaiting data ' );
  }

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
  public function addDataMapper( DataMapper $dataMapper )
  {
    $dataMapper->setImporter( $this );
    $this->dataMapper[] = $dataMapper;
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
    $this->output( 'run ' );
    foreach( $this->getDataMappers() as $dataSource )
    {
      foreach( $dataSource->getMapMethods() as $mapMethod )
      {
         $mapMethod->invoke( $dataSource );
      }
    }
  }

  /**
   * @todo implement logger
   * 
   * Listens to DataMapper notifications
   * 
   * @param Doctrine_Record $record
   */
  public function onRecordMapped( Doctrine_Record $record )
  {
    //record exists?
    //transform( $records )
    if( $record->isValid( true ) )
    {
      $record->save();
      $this->output( '.' );
      //log save|update
    }
    else
    {
      //echo $record->getErrorStackAsString();
      $this->output( 'x' );
    }
  }

  /**
   * print some output
   * @param string $output
   */
  private function output( $output )
  {
    if( $this->printProgress )
    {
      echo $output;
    }
  }
}
