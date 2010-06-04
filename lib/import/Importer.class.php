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
  private $dataMapper = array();
  
  public function __construct( $printProgress = false )
  {
    $this->printProgress = $printProgress;
    $this->output( 'awaiting data ' );
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
   * 
   * Listens to DataMapper notifications
   * 
   * @param RecordData $recordData
   */
  public function onRecordMapped( Doctrine_Record $record )
  {
     ImportLogger::saveRecordComputeChangesAndLog( $record );
  }

  public function onRecordMappingException( Exception $exception, Doctrine_Record $record = NULL, $message = '' )
  {
    if( $record ) ImportLogger::getInstance()->addFailed( $record );
    ImportLogger::getInstance()->addError( $exception, $record, $message );
  }

  /**
   * @param RecordData $data
   * @returns Doctrine_Record
   */
  protected function getRecordUsingData( RecordData $recordData )
  {
    $data = $recordData->getData();

    $recordClass = $recordData->getClass();

    if( !is_null( $data[ 'id' ] ) )
    {
      $record = Doctrine::getTable( $recordClass )->findOneById( $data[ 'id' ] ); 
    }
    else
    {
      $record = new $recordClass();
    }

    $record->fromArray( $data );


    /* START workaround: http://www.doctrine-project.org/jira/browse/DC-242 ?*/
    $relations = $record->getReferences();
    foreach( $relations as $relation => $related )
    {
      $record[ $relation ] = $related;
    }
    $record->clearRelated();
    /* END work around*/

    return $record;
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
