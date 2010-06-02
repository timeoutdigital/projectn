<?php
/* 
 * Logger class to log inserts and updates.
 */

/**
 * Description of loggerclass
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Tim bowler <timbowler@timeout.com>
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.1
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
 */
class logImport
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
     * @var string
     */
    private $_timer;

    /**
     *
     * @var ImportLogger
     */
    private $_importLogger;


    /**
     *
     * Constructor
     *
     * @param Vendor $vendorObj
     * @param log type $type
     */
    public function  __construct( Vendor $vendorObj )
    {
        $this->_vendorObj = $vendorObj;

        $this->_importLogger = new ImportLogger;
        //$this->_importLogger['total_received'] = $this->_totalReceived;
        $this->_importLogger['Vendor']         = $this->_vendorObj;
        $this->_importLogger['status']         = 'running';
        $this->_importLogger['total_time']     = '00:00';
        $this->_importLogger->save();

        $this->_timer = new sfTimer( 'importTimer' );
        $this->_timer->startTimer();
    }    

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalInserts( $limitByModel = '' )
    {
        return $this->_count( 'insert', $limitByModel );
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalUpdates( $limitByModel = '' )
    {
        return $this->_count( 'update', $limitByModel );
    }

    /**
     *
     * @return integer
     */
    public function getTotalExisting( $limitByModel = '' )
    {
        return $this->_count( 'existing', $limitByModel  );
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalDeletes( $limitByModel = '' )
    {
        return $this->_count( 'delete', $limitByModel );
    }

    /**
     *
     * @return integer
     */
    public function getTotalErrors()
    {
        return $this->_importLogger['ImportRecordErrorLogger']->count();
    }

    /**
     *
     * @return integer
     */
    public function getTotalReceived()
    {
        return $this->_totalReceived;
    }

    /**
     * Save the stats
     */
    public function save( $echoMessage = '' )
    {   
        //$this->_importLogger['total_received'] = $this->_totalReceived;
        $this->_importLogger['total_time'] = $this->_getElapsedTime();
        $this->_importLogger->save();

        $this->_echo( $echoMessage );
    }

    /**
     *
     * @param string $model
     * @param integer $totalReceived
     */
    public function setTotalReceivedItems( $model, $totalReceived )
    {
        $importRecordErrorLogger = null;
        
        foreach ( $this->_importLogger[ 'ImportLoggerReceived' ] as $existingImportRecordErrorLogger )
        {
           if ( $existingImportRecordErrorLogger[ 'model' ] == $model )
           {
               $importRecordErrorLogger = $existingImportRecordErrorLogger;
               break;
           }
        }
        
        if ( is_null( $importRecordErrorLogger  ) )
        {
            $importRecordErrorLogger = new ImportLoggerReceived();
            $importRecordErrorLogger['model'] = $this->_checkModel( $model );
        }

        $importRecordErrorLogger['total_received'] = $totalReceived;


        //@todo make sure we dont duplicate here for existing ones
        $this->_importLogger[ 'ImportLoggerReceived' ][] = $importRecordErrorLogger;
        
        $echoMessage = 'set total received items for model ' . $model . ' to: ' .$totalReceived . 'items';

        $this->save( $echoMessage );
    }

    /**
     * Log a successful database operation (insert and update implemented)
     *
     * @param Doctrine_Record $newObject
     * @param string $operation ( logImport::INSERT or logImport::UPDATE )
     * @param array $changedFields
     *
     * @todo implement delete
     */
    public function addSuccess( Doctrine_Record $newObject, $operation, $changedFields = array() )
    {
        $operation = $this->_checkOperation( $operation );

        switch( $operation )
        {
            case logImport::OPERATION_INSERT :
                $this->_addInsert( $newObject );
                break;
            case logImport::OPERATION_UPDATE :
                $this->_addUpdate( $newObject, $changedFields );
                break;            
            //case logImport::OPERATION_DELETE :
            //    break;
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
    public function addError(Exception $error, Doctrine_Record $record = NULL, $log = '')
    {
        $importRecordErrorLogger                     = new ImportRecordErrorLogger();
        $importRecordErrorLogger['model']            = get_class( $record) ;
        $importRecordErrorLogger['exception_class']  = get_class( $error );
        $importRecordErrorLogger['trace']            = $error->__toString();
        $importRecordErrorLogger['message']          = $error->getMessage();
        $importRecordErrorLogger['log']              = $log;
        
        if ( $record !==  NULL)
        {
            $importRecordErrorLogger['serialized_object']        = serialize( $record );
        }

        $this->_importLogger[ 'ImportRecordErrorLogger' ][] = $importRecordErrorLogger;

        $echoMessage = 'error on model ' . $importRecordErrorLogger['model'] . ' trace: ' . $importRecordErrorLogger['trace'];

        $this->save( $echoMessage );
    }

    /**
     * ends a log and marks it as successful
     * (the importLogger object will be destructed)
     */
    public function endSuccessful()
    {
        $this->_importLogger['status'] = 'success';
        $this->save();
        unset( $this->_importLogger );
    }

    /**
     * ends a log and marks it as failed
     * (the importLogger object will be destructed)
     */
    public function endFailed()
    {
        $this->_importLogger['status'] = 'failed';
        $this->save();
        unset( $this->_importLogger );
    }

    /**
     * check if import logger is still running
     *
     * @return boolean
     */
    public function checkIfRunning()
    {
        return $this->_importLogger instanceof ImportLogger;
    }

     /**
     * Log an insert
     *
     * @param object $record
     */
    private function _addInsert( Doctrine_Record $record )
    {
      $importRecordLogger = new ImportRecordLogger();
      $importRecordLogger[ 'record_id' ] = $record[ 'id' ];
      $importRecordLogger[ 'model' ] = get_class( $record );
      $importRecordLogger['operation'] = 'insert';

      $this->_importLogger[ 'ImportRecordLogger' ][] = $importRecordLogger;

      $echoMessage = 'insert into model ' . $importRecordLogger[ 'model' ] . ' id: ' . $record[ 'id'] .  ' / name: ' . $record[ 'name'] ;

      $this->save( $echoMessage );
    }


    /**
     * Log an update
     *
     * @param object $record
     * @param string $log Log of all updates
     */
    private function _addUpdate( Doctrine_Record $record, $modifiedFieldsArray )
    {
      $importRecordLogger = new ImportRecordLogger();
      $importRecordLogger[ 'record_id' ] = $record[ 'id' ];
      $importRecordLogger[ 'model' ] = get_class( $record );

      if ( count( $modifiedFieldsArray ) == 0 )
      {
          $importRecordLogger['operation']   = 'existing';
      }
      else
      {
          $log = "Updated Fields: \n";

          //The item is modified therefore log as an update
          foreach( $modifiedFieldsArray as $k => $v )
          {
              $log .= "$k: $v \n";
          }
          $importRecordLogger['log']         = $log;
          $importRecordLogger['operation']   = 'update';
      }

      $this->_importLogger[ 'ImportRecordLogger' ][] = $importRecordLogger;

      $echoMessage = 'update on model ' . $importRecordLogger[ 'model' ] . ' id: ' . $record[ 'id'] .  ' / name: ' . $record[ 'name'] ;

      $this->save( $echoMessage );
    }

    /**
     * Check if the log operation is valid
     *
     * @param string $operation
     * @return string $operation
     */
    private function _checkOperation( $operation )
    {
        $availableOperations = array( logImport::OPERATION_INSERT, logImport::OPERATION_UPDATE );

        if( !in_array( $operation, $availableOperations ) )
        {
            throw new Exception('Incorrect Operation (' .$operation. '), must be : ' . implode( ',', $availableOperations ) );
        }

        return $operation;
    }

    private function _checkModel( $model )
    {
        if( !Doctrine::isValidModelClass( $model ) )
        {
            throw new Exception('Incorrect Model (' .$model. ') specified' );
        }

        return $model;
    }
    
    /**
     *
     * @return time
     */
    private function _getElapsedTime()
    {
        $this->_timer->addTime();
        $this->_timer->startTimer();
        $seconds = $this->_timer->getElapsedTime();

        $timeStamp = mktime( 0, 0, $seconds );

        return date('H:i:s', $timeStamp);
    }
    
    /**
     * Count types ('integer', 'update', 'existing or 'delete')
     * 
     * @param string $operation
     * @param string $limitByModel
     * @return integer
     */
    private function _count( $operation, $limitByModel = '' )
    {
        $count = 0;
        
        foreach( $this->_importLogger[ 'ImportRecordLogger' ] as $success )
        {
            if ( $operation == $success['operation'] && ( $limitByModel == '' || $limitByModel == $success[ 'model' ] ) )
            {
                $count++;
            }
        }
        
        return $count;        
    }

    private function _echo( $message )
    {
        echo 'IMPORT LOGGER: ' . $message . PHP_EOL;
    }

}
?>
