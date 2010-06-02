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
     * @var integer
     */
    private $_totalReceived = 0;

    /**
     *
     * @var Vendor
     */
    private $_vendorObj;

    /**
     *
     * @var ImportLogger
     */
    private $_importLog;


    /**
     *
     * Constructor
     *
     * @param Vendor $vendorObj
     * @param log type $type
     */
    public function  __construct( Vendor $vendorObj )
    {
        parent::__construct();

        $this->_vendorObj = $vendorObj;

        $this->_importLog = new LogImport;
        //$this->_importLog['total_received'] = $this->_totalReceived;
        $this->_importLog['Vendor']         = $this->_vendorObj;
        $this->_importLog['status']         = 'running';
        $this->_importLog['total_time']     = '00:00';
        $this->_importLog->save();
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
        return $this->_importLog['LogImportChange']->count();
    }

    /**
     *
     * @return integer
     */
    public function getTotalErrors()
    {
        return $this->_importLog['LogImportError']->count();
    }

    /**
     * Save the stats
     */
    public function save()
    {
        //$this->_importLog['total_received'] = $this->_totalReceived;
        $this->_importLog['total_time'] = $this->_getElapsedTime();
        $this->_importLog->save();
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
    public function addError(Exception $error, Doctrine_Record $record = NULL, $log = '')
    {
        $importRecordErrorLogger                     = new LogImportError();
        $importRecordErrorLogger['model']            = get_class( $record) ;
        $importRecordErrorLogger['exception_class']  = get_class( $error );
        $importRecordErrorLogger['trace']            = $error->__toString();
        $importRecordErrorLogger['message']          = $error->getMessage();
        $importRecordErrorLogger['log']              = $log;

        if ( $record !==  NULL)
        {
            $importRecordErrorLogger['serialized_object']        = serialize( $record );
        }

        $this->_importLog[ 'LogImportError' ][] = $importRecordErrorLogger;
        $this->save( );
    }

     /**
     * Log an insert
     *
     * @param object $record
     */
    public function addInsert( Doctrine_Record $record )
    {
       $logImportCount = $this->_getLogImportCountObject( 'insert', get_class( $record ) );
        $logImportCount[ 'count' ] = $logImportCount[ 'count' ] + 1;
       $this->save( );
    }

    /**
     *
     * @param string $model
     * @param integer $totalReceived
     */
    public function addReceived( $model )
    {
       $logImportCount = $this->_getLogImportCountObject( 'received', get_class( $record ) );
       $logImportCount[ 'count' ] = $logImportCount[ 'count' ] + 1;
       $this->save( );
    }

    /**
     * Log an update
     *
     * @param object $record
     * @param string $log Log of all updates
     */
    public function addUpdate( Doctrine_Record $record, $modifiedFieldsArray )
    {  
      if ( count( $modifiedFieldsArray ) == 0 )
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

          $this->_importLog[ 'LogImportChange' ][] = $logImportChange;
      }
      
      $this->save( );
    }


    /**
     * ends a log and marks it as successful
     * (the importLogger object will be destructed)
     */
    public function endSuccessful()
    {
        $this->_importLog['status'] = 'success';
        $this->save();
        unset( $this->_importLog );
    }

    /**
     * ends a log and marks it as failed
     * (the importLogger object will be destructed)
     */
    public function endFailed()
    {
        $this->_importLog['status'] = 'failed';
        $this->save();
        unset( $this->_importLog );
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

        if ( $limitByModel != '' )
        {
            $logImportCount = $this->_getLogImportCountObject( $operation, $limitByModel );
            return $logImportCount[ 'count' ];
        }
        else
        {
            $counter = 0;

            foreach( $this->_importLog[ 'LogImportCount' ] as $logImportCount )
            {
                if ( $logImportCount[ 'operation' ] == $operation )
                    $counter += $logImportCount[ 'count' ];
            }

            return $counter;
        }
    }



    private function _getLogImportCountObject( $operation, $model  )
    {
        foreach( $this->_importLog[ 'LogImportCount' ] as $logImportCount )
        {
            if ( $logImportCount[ 'model' ] == $model &&  $logImportCount[ 'operation' ] == $operation )
                return $logImportCount;
        }

        $logImportCount = new LogImportCount();
        $logImportCount[ 'model' ] = $model;
        $logImportCount[ 'operation' ] = $operation;
        $logImportCount[ 'count' ] = 0;
        $this->_importLog[ 'LogImportCount' ][] = $logImportCount;

        return $logImportCount;
    }



}

?>