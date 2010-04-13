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
 * $movieLoggerObj->addError( $exceptionObject, $recordObject, 'Something went wrong here')
 *
 * //Log Successful DB operation
 * $movieLoggerObj->addSuccess( $recordObject, logImport::OPERATION_INSERT, $changedFields )
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
class logImport implements loggable
{
    // available type constants
    const TYPE_POI = 'poi';
    const TYPE_EVENT = 'event';
    const TYPE_MOVIE = 'movie';

    // available operations constants
    const OPERATION_INSERT = 'insert';
    const OPERATION_UPDATE = 'update';
    //const DELETE = 'delete';


    /**
     * @var integer
     */
    private $_totalReceived = 0;

    /**
     *
     * @var integer
     */
    private $_totalExisting = 0;

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
    public function  __construct( Vendor $vendorObj, $type )
    {
        $this->_vendorObj = $vendorObj;

        $this->_importLogger = new ImportLogger;
        $this->_importLogger['total_received'] = $this->_totalReceived;
        $this->_importLogger['total_existing'] = $this->_totalExisting;
        $this->_importLogger['type']           = $this->_checkType( $type );
        $this->_importLogger['Vendor']         = $this->_vendorObj;
        $this->_importLogger['status']         = 'running';
        $this->_importLogger['total_time']     = '00:00';
        $this->_importLogger->save();

        $this->_timer = new sfTimer( 'importTimer' );
        $this->_timer->startTimer();
    }    

    /**
     * 
     * @return integer
     */
    public function getTotalInserts()
    {
        return $this->_count( 'insert' );
    }

    /**
     *
     * @return integer
     */
    public function getTotalUpdates()
    {
        return $this->_count( 'update' );
    }

    /**
     *
     * @return integer
     */
    public function getTotalDeletes()
    {
        return $this->_count( 'delete' );
    }

    /**
     *
     * @return integer
     */
    public function getTotalErrors()
    {
        return $this->_importLogger['ImportLoggerError']->count();
    }

    /**
     *
     * @return integer
     */
    public function getTotalExisting()
    {
        return $this->_totalExisting;
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
     *
     * @param integer $totalReceived
     */
    public function setTotalReceived( $totalReceived )
    {
        $this->_totalReceived = $totalReceived;
    }

    /**
     * Save the stats
     */
    public function save()
    {   
        $this->_importLogger['total_received'] = $this->_totalReceived;
        $this->_importLogger['total_existing'] = $this->_totalExisting;
        $this->_importLogger['total_time'] = $this->_getElapsedTime();
        $this->_importLogger->save();
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
        $errorObj                     = new ImportLoggerError();
        $errorObj['import_logger_id'] = $this->_importLogger[ 'id' ];
        $errorObj['trace']            = $error->__toString();
        $errorObj['log']              = $log;
        $errorObj['type']             = get_class($error);
        $errorObj['message']          = $error->getMessage();

        if ( $record !==  NULL)
        {
            $errorObj['serialized_object']        = serialize( $record );
        }

        $this->_importLogger[ 'ImportLoggerError' ][] = $errorObj;

        $this->save();

        //Increment the error count
        $this->_totalErrors++;
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
     * count each record that already exists
     */
    private function _countUpExisting()
    {
        $this->_totalExisting++;
    }

     /**
     * Log an insert
     *
     * @param object $newObject
     */
    private function _addInsert( Doctrine_Record $newObject )
    {
      $changeObj         = new ImportLoggerSuccess();
      $changeObj['type'] = 'insert';

      $changeObj[ get_class( $newObject ) ][] = $newObject;

      $this->_importLogger[ 'ImportLoggerSuccess' ][] = $changeObj;

      $this->save();
    }


    /**
     * Log an update
     *
     * @param object $newObject
     * @param string $log Log of all updates
     */
    private function _addUpdate( Doctrine_Record $newObject, $modifiedFieldsArray )
    {
      if ( count( $modifiedFieldsArray ) == 0 )
      {
          $this->_countUpExisting();
      }

      $log = "Updated Fields: \n";

      //The item is modified therefore log as an update
      foreach( $modifiedFieldsArray as $k => $v )
      {
          $log .= "$k: $v \n";
      }

      $changeObj         = new ImportLoggerSuccess();
      $changeObj['log']  = $log;
      $changeObj['type'] = 'update';
      $changeObj[ get_class( $newObject ) ][] = $newObject;

      $this->_importLogger[ 'ImportLoggerSuccess' ][] = $changeObj;

      $this->save();
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
            throw new Exception('Incorrect Operation, must be : ' . implode( ',', $availableOperations ) );
        }

        return $operation;
    }

    /**
     * Check if the log type is valid
     *
     * @param string $type
     * @return string $type
     */
    private function _checkType( $type )
    {
        $availableTypes = array( logImport::TYPE_POI, logImport::TYPE_EVENT, logImport::TYPE_MOVIE );

        if( !in_array( $type, $availableTypes ) )
        {
            throw new Exception('Incorrect Type. Must be on of: ' . implode( ',', $availableTypes ) );
        }

        return $type;
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
     * Count types ('integer', 'update' or 'delete')
     * 
     * @param string $type
     * @return integer
     */
    private function _count( $type )
    {
        $count = 0;
        
        foreach( $this->_importLogger[ 'ImportLoggerSuccess' ] as $success )
        {
            if ( $type == $success['type'] )
            {
                $count++;
            }
        }
        
        return $count;        
    }
}
?>
