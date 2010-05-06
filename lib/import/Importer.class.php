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
   * @param logImport $logger
   */
  public function addLogger( loggable $logger )
  {
    if( !in_array( $logger, $this->loggers ) )
    {
      $this->loggers[] = $logger;
    }
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

    foreach( $this->loggers as $logger )
    {
      $logger->save();
    }
  }

  /**
   * @todo implement logger
   * 
   * Listens to DataMapper notifications
   * 
   * @param RecordData $recordData
   */
  public function onRecordMapped( Doctrine_Record $record )
  {
     try
     {
        //get the state of the record before save
        $recordIsNew = $record->isNew();
        $recordModifications = $record->getModified();

        $record->save();
        
        //if record is saved, notify the logger
        if( $recordIsNew )
        {
          foreach( $this->loggers as $logger )
          {
            $logger->countNewInsert();
          }
        }
        else if( !empty( $recordModifications ) )
        {
//          foreach( $this->loggers as $logger )
//          {
//            $logger->addChange( 'update', $recordModifications );
//            $logger->countExisting();
//          }
        }
     }
     catch( Exception $e )
     {
         $this->onRecordMappingException( $e ,$record  );
     }
  }

  public function onRecordMappingException( Exception $exception, Doctrine_Record $record = NULL, $message = '' )
  {
     foreach( $this->loggers as $logger )
     {
       $logger->addError( $exception ,$record , $message );
     }
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
