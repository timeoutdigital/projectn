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
     try
     {
        // Empty Array to store field modification info.
        $modified = array();
         
        //get the state of the record before save
        $recordIsNew = $record->isNew();

        if( !$recordIsNew )
            $oldRecord = Doctrine::getTable( get_class( $record ) )->findOneById( $record->id, Doctrine::HYDRATE_ARRAY );
        
        $record->save();

        // If Record is not new, check to see which fields are modified.
        // Do it like this because Doctrine lastModified function(s) mark fields as modified
        // if they have been set and reset in the current script execution, regardless of their
        // original database state.
        if( !$recordIsNew )
        {
            $newRecord = $record->toArray( false );
            
            foreach( $newRecord as $key => $mod )
                if( $key != "updated_at" && array_key_exists( $key, $oldRecord ) )
                    if( $newRecord[ $key ] != $oldRecord[ $key ] )
                        $modified[ $key ] = "'" . $oldRecord[ $key ] . "'->'" . $newRecord[ $key ] . "'";

            unset( $oldRecord, $newRecord );
        }

        if ( $recordIsNew )
            ImportLogger::getInstance()->addInsert( $record );

        else ImportLogger::getInstance()->addUpdate( $record, $modified );
     }
     catch( Exception $e )
     {
         $this->onRecordMappingException( $e ,$record  );
     }
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
