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
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 *
 * //Instaniate
 * $movieLoggerObj = new logger($vendorObj, 'movie');
 * $poiLoggerObj = new logger($vendorObj, 'poi');
 *
 *
 * //user
 * $movieLoggerObj->countNewInsert()
 *
 * //Save the log
 * $movieLoggerObj->saveStats();
 *
 * </code>
 *
 */
class logImport implements loggable
{

    const POI = 'poi';
    const EVENT = 'event';
    const MOVIE = 'movie';

    const INSERT = 'insert';
    const UPDATE = 'update';
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
    private $_type;

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
     * @param int $vendorId
     */
    public function  __construct( Vendor $vendorObj, $type = 'poi' )
    {
        $this->_vendorObj = $vendorObj;

        $this->_type = $this->checkType($type);

        $this->_importLogger = new ImportLogger;
        $this->_importLogger['total_received'] = $this->_totalReceived;
        $this->_importLogger['type']           = $this->_type;
        $this->_importLogger['Vendor']         = $this->_vendorObj;
        $this->_importLogger['total_existing'] = $this->_totalExisting;
        $this->_importLogger['status']         = 'running';
        $this->_importLogger['total_time']     = '00:00';
        $this->_importLogger->save();

        $this->_timer = new sfTimer( 'importTimer' );
        $this->_timer->startTimer();
    }
    


    public function countNewInsert();
     public function countExisting();


    /**
     * 
     * @return integer
     */
    public function getTotalInserts()
    {
        return $this->_totalInserts;
    }

    /**
     *
     * @return integer
     */
    public function getTotalUpdates()
    {
        
        
        var_export( Doctrine::getLoadedModels($this) );
        
        return $this->_importLogger['ImportLoggerSuccess']->count();


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
        $this->_importLogger['total_existing'] = $this->_totalExisting;  
        $this->_importLogger['total_time'] = $this->_getElapsedTime();
        $this->_importLogger->save();
    }







    public function addSuccess( Doctrine_Record $newObject, $operation, $changedFields = array() )
    {
        $availableOperations = array( logImport::INSERT, logImport::UPDATE );

        if( !in_array( $operation, $availableOperations ) )
        {
            throw new Exception('Incorrect Operation, must be : ' . implode( ',', $availableOperations ) );
        }

        switch( $operation )
        {
            case logImport::INSERT :
                $this->_addInsert( $newObject );
                break;
            case logImport::UPDATE :
                $this->_addUpdate( $newObject, $changedFields );
                break;
            case logImport::DELETE :
                break;
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
    private function _countExisting()
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

      $changeObj[ $newObject->getRecordType() ] = $newObject;

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
          $this->_countExisting();
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
     * Check the type going in
     *
     * @param string $type
     * @return string $type
     */
    private function checkType($type)
    {
        $availableTypes = array( logImport::POI, logImport::EVENT, logImport::MOVIE );
     
        if( !in_array( $type, $availableTypes ) )
        {
            throw new Exception('Incorrect Type. Must be on of: ' . implode( ',', $availableTypes ) );
        }

        $this->_type = $type;

        return $this->_type;
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
}
?>
