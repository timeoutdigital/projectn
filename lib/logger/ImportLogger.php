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
    private $_enabled = true;

    /**
     *
     * @var Progressive Save
     * @desc Save Every Request (true)
     * .. or just at the end (true && false)
     */
    private $_progressiveSave = false;

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
            return $this->_importLoggers[ $currentCity ];
        }
    }

    public function enabled( $bool = true )
    {
        if( is_bool( $bool ) ) $this->_enabled = $bool;
    }

    public function progressive( $bool = true )
    {
        if( is_bool( $bool ) ) $this->_progressiveSave = $bool;
    }

    /**
     *
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
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalReceived( $limitByModel = '' )
    {
        return $this->_getCount( 'received', $limitByModel );
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalInserts( $limitByModel = '' )
    {
        return $this->_getCount( 'insert', $limitByModel );
    }

    /**
     *
     * @return integer
     */
    public function getTotalExisting( $limitByModel = '' )
    {
        return $this->_getCount( 'existing', $limitByModel  );
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalDeletes( $limitByModel = '' )
    {
        return $this->_getCount( 'delete', $limitByModel );
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalUpdates( $limitByModel = '' )
    {
        $importLogger = $this->getLoggerByVendor();
        return $importLogger['LogImportChange']->count();
    }

    /**
     *
     * @return integer
     */
    public function getTotalErrors()
    {
        $importLogger = $this->getLoggerByVendor();
        return $importLogger['LogImportError']->count();
    }

    /**
     * Save the stats
     */
    public function save()
    {
        if( $this->_enabled )
        {
            $importLogger = $this->getLoggerByVendor();
            $importLogger['total_time'] = $this->_getElapsedTime();
            if( $this->_progressiveSave ) $importLogger->save();
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

            if ( $record !==  NULL)
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
     * Log an failure
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
     *
     * @param string $model
     * @param integer $totalReceived
     */
    public function addReceived( $model )
    {
       if( $this->_enabled )
       {
           $logImportCount = $this->_getLogImportCountObject( 'received', get_class( $record ) );
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
    public function addUpdate( Doctrine_Record $record, $modifiedFieldsArray )
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
     * ends a log and marks it as successful
     * (the importLogger object will be destructed)
     */
    public function endSuccessful()
    {
        if( $this->_enabled )
        {
            $importLogger = $this->getLoggerByVendor();
            $importLogger['status'] = 'success';
            $this->save();
        }

        unset( $this->_importLoggers[ $this->_vendorObj['city'] ] );
    }

    /**
     * ends a log and marks it as failed
     * (the importLogger object will be destructed)
     */
    public function endFailed()
    {
        if( $this->_enabled )
        {
            $importLogger = $this->getLoggerByVendor();
            $importLogger['status'] = 'failed';
            $this->save();
        }

        unset( $this->_importLoggers[ $this->_vendorObj['city'] ] );
    }

    /**
     * Count types ('integer', 'update', 'existing or 'delete')
     *
     * @param string $operation
     * @param string $limitByModel
     * @return integer
     */
    private function _getCount( $operation, $limitByModel = '' )
    {
        $importLogger = $this->getLoggerByVendor();
        
        if ( $limitByModel != '' )
        {
            $logImportCount = $this->_getLogImportCountObject( $operation, $limitByModel );
            return $logImportCount[ 'count' ];
        }
        else
        {
            $counter = 0;

            foreach( $importLogger[ 'LogImportCount' ] as $logImportCount )
            {
                if ( $logImportCount[ 'operation' ] == $operation )
                    $counter += $logImportCount[ 'count' ];
            }

            return $counter;
        }
    }



    private function _getLogImportCountObject( $operation, $model  )
    {
        $importLogger = $this->getLoggerByVendor();
        
        foreach( $importLogger[ 'LogImportCount' ] as $logImportCount )
        {
            if ( $logImportCount[ 'model' ] == $model &&  $logImportCount[ 'operation' ] == $operation )
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

            if( isset( $record ) ) $record->free( true );
        }
        catch( Exception $e )
        {
            if( $record ) ImportLogger::getInstance()->addFailed( $record );
            ImportLogger::getInstance()->addError( $e, $record, 'failed to save record' );
            
            if( isset( $record ) ) $record->free( true );
        }
    }
}

class ImportLoggerException extends Exception {}

?>