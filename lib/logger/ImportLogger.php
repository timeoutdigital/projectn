<?php
/*
 * ImportLogger
 *
 * @package projectn
 * @subpackage logger.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.1
 *
 *
 * <b>Example</b>
 * <code>
 *
 * //Instantiate
 * $this->object = new logImport( $this->vendorObj, logImport::TYPE_MOVIE );
 *
 * //Log Error
 * $movieLoggerObj->addError( $exceptionObject, $recordObject, 'Something went wrong here');
 *
 * //Log Successful DB operation
 * $movieLoggerObj->addSuccess( $recordObject, logImport::OPERATION_INSERT, $changedFields );
 *
 * //End Successfull
 * $movieLoggerObj->endSuccessful();
 *
 * //In a try catch it can be ended as failure
 * $movieLoggerObj->endFailed();
 *
 * </code>
 *
 *
 */

class ImportLogger extends BaseLogger
{

    /**
     *
     * @var Vendor
     */
    private $_vendorObj;

    /**
     *
     * @var Enabled
     */
    private $_enabled = false;

    /**
     *
     * @var Progressive Save
     * @desc Save Every Request (true)
     * .. or just at the end (true && false)
     */
    private $_progressiveSave = false;

    /**
     *
     * @var saveEvery
     * @desc Save Every x Records
     */
    private $_maxInCache = 50;

    /**
     *
     * @var recordsInCache
     * @desc How Many Records Waiting to be Saveds
     */
    private $_recordsInCache = 0;

    /**
     *
     * @var UnknownVendor
     */
    private $_unknownVendor;

    /**
     *
     * @var ImportLogger
     */
    private $_importLoggers = array();

    /**
     *
     * @var instance
     */
    private static $instance;


    /**
     * Constructor (protected)
     */
    protected function  __construct()
    {
        $this->_unknownVendor = Doctrine::getTable("Vendor")->findOneByCity( 'unknown' );
        parent::__construct();
    }

    /**
     * unsetSingleton()
     * Used by Unit Tests to reset singleton object between tests.
     */
    public function unsetSingleton()
    {
        $c = __CLASS__;
        self::$instance = new $c;
    }

    /**
     * getRecordsInCache()
     * Only used for testing.
     * Returns number of records currently stored in cache waiting to be saved.
     */
    public function getRecordsInCache()
    {
        return $this->_recordsInCache;
    }

    /**
     * getMaxInCache()
     * Only used for testing.
     * Returns maximum number of records to store in cache.
     */
    public function getMaxInCache()
    {
        return $this->_maxInCache;
    }

    /**
     * getLoggerByVendor()
     * Gets appropriate vendor, esp useful for multiple vendor feeds.
     */
    private function getLoggerByVendor()
    {
        if( !$this->_vendorObj ) $this->setVendorUnknown();
                
        $currentCity = $this->_vendorObj['city'];
        
        if( array_key_exists( $currentCity, $this->_importLoggers ) )
            return $this->_importLoggers[ $currentCity ];
        else {
            $this->_importLoggers[ $currentCity ]                   = new LogImport;
            $this->_importLoggers[ $currentCity ]['Vendor']         = $this->_vendorObj;
            $this->_importLoggers[ $currentCity ]['status']         = 'running';
            $this->save( true );
            return $this->_importLoggers[ $currentCity ];
        }
    }

    /**
     * enabled()
     * Setter, enable or disable ImportLogger.
     */
    public function enabled( $bool = true )
    {
        if( is_bool( $bool ) ) $this->_enabled = $bool;
    }

    /**
     * progressive()
     * Setter, enable or disable progressive saving (true = save every time).
     */
    public function progressive( $bool = true )
    {
        if( is_bool( $bool ) ) $this->_progressiveSave = $bool;
    }

    /**
     * end()
     * Call this when the import ends.
     */
    public function end()
    {
        if( $this->_enabled )
            foreach( $this->_importLoggers as $importLogger )
            {
                $importLogger['status']         = 'success';
                $importLogger['total_time']     = $this->_getElapsedTime();
                $importLogger->save();
            }
    }

    /**
     *
     * @param string $vendorObj
     * @return $this
     */
    public function setVendor( Vendor $vendorObj )
    {
        $this->_vendorObj = $vendorObj;
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function setVendorUnknown()
    {
        $this->_vendorObj = $this->_unknownVendor;
        return $this;
    }


    /**
     * Save the stats
     */
    public function save( $forceSaveNow = false )
    {
        if( $this->_enabled )
        {            
            $this->_recordsInCache++;
            if( $this->_progressiveSave || $this->_recordsInCache >= $this->_maxInCache || $forceSaveNow )
            {
                foreach( $this->_importLoggers as $importLoggerCity => $importLogger )
                {
                    $importLogger['total_time'] = $this->_getElapsedTime();
                    $importLogger->save();

                    // Reload Logger Object to remove old references from RAM (don't think this works!?)
                    //$this->_importLoggers[ $importLoggerCity ] = Doctrine::getTable("LogImport")->findOneById( $importLogger['id'] );
                }
                
                $this->_recordsInCache = 0;
            }
        }
    }

    /**
     *
     * Add an error to be logged.
     *
     * @param Exception $error The exception thrown by the error
     * @param Doctrine_Record $record The record causeing the error
     * @param string $log Any extra details that can help someone solve this error
     *
     */
    public function addError( Exception $error, $record = NULL, $log = '' )
    {
        if( $this->_enabled )
        {
            $importRecordErrorLogger                     = new LogImportError();
            $importRecordErrorLogger['exception_class']  = get_class( $error );
            $importRecordErrorLogger['trace']            = $error->__toString();
            $importRecordErrorLogger['message']          = $error->getMessage();
            $importRecordErrorLogger['log']              = $log == '' ? "@todo - no log message" : $log;

            if ( is_subclass_of( $record, "Doctrine_Record" ) )
            {
                $importRecordErrorLogger['model']        = get_class( $record );
                $storeObject = method_exists( 'toArray', $record ) ? $record : $record->toArray();
                $importRecordErrorLogger['serialized_object']        = serialize( $storeObject );
            }

            $importLogger = $this->getLoggerByVendor();
            $importLogger[ 'LogImportError' ][] = $importRecordErrorLogger;
            $this->save();
        }
    }

     /**
     * Log an insert
     *
     * @param object $record
     */
    public function addInsert( Doctrine_Record $record )
    {
       if( $this->_enabled )
       {
           $logImportCount = $this->_getLogImportCountObject( 'insert', get_class( $record ) );
           $logImportCount[ 'count' ] = $logImportCount[ 'count' ] + 1;
           $this->save();
       }
    }

     /**
     * Log a failure
     *
     * @param object $record
     */
    public function addFailed( Doctrine_Record $record )
    {
       if( $this->_enabled )
       {
           $logImportCount = $this->_getLogImportCountObject( 'failed', get_class( $record ) );
           $logImportCount[ 'count' ] = $logImportCount[ 'count' ] + 1;
           $this->save();
       }
    }

    /**
     * Log an update
     *
     * @param object $record
     * @param string $log Log of all updates
     */
    public function addUpdate( Doctrine_Record $record, $modifiedFieldsArray = array() )
    {
      if( $this->_enabled )
      {
          if ( empty( $modifiedFieldsArray ) )
          {
             $logImportCount = $this->_getLogImportCountObject( 'existing', get_class( $record ) );
             $logImportCount[ 'count' ] = $logImportCount[ 'count' ] + 1;
          }
          else
          {
              $log = "Updated Fields: \n";

              //The item is modified therefore log as an update
              foreach( $modifiedFieldsArray as $k => $v )
              {
                  $log .= "$k: $v \n";
              }
              $logImportChange = new LogImportChange();
              $logImportChange[ 'record_id' ] = $record[ 'id' ];
              $logImportChange[ 'model' ]     = get_class( $record );
              $logImportChange['log']         = $log;

              $importLogger = $this->getLoggerByVendor();
              $importLogger[ 'LogImportChange' ][] = $logImportChange;
          }

          $this->save();
      }
    }

    /**
     * _getLogImportCountObject()
     * Used privately
     */
    private function _getLogImportCountObject( $operation, $model  )
    {
        $importLogger = $this->getLoggerByVendor();
        
        foreach( $importLogger[ 'LogImportCount' ] as $logImportCount )
        {
            if ( $logImportCount[ 'model' ] == $model && $logImportCount[ 'operation' ] == $operation )
                return $logImportCount;
        }

        $logImportCount = new LogImportCount();
        $logImportCount[ 'model' ] = $model;
        $logImportCount[ 'operation' ] = $operation;
        $logImportCount[ 'count' ] = 0;
        $importLogger[ 'LogImportCount' ][] = $logImportCount;

        return $logImportCount;
    }

    
    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /**
     * saveRecordComputeChangesAndLog()
     * Wrapper for $record->save()
     * Computes modifications, saves object and records stats, logs errors.
     */
    public static function saveRecordComputeChangesAndLog( &$record )
    {
        try
        {
            if( !is_subclass_of( $record, "Doctrine_Record" ) )
                throw new ImportLoggerException( "Record Passed To ImportLogger::saveRecordComputeChangesAndLog is not extended from Doctrine_Record" );

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

            //if( isset( $record ) ) $record->free( true );
        }
        catch( Exception $e )
        {
            if( $record ) ImportLogger::getInstance()->addFailed( $record );
            ImportLogger::getInstance()->addError( $e, $record, 'failed to save record' );
            
            //if( isset( $record ) ) $record->free( true );
        }
    }
}

class ImportLoggerException extends Exception {}

?>