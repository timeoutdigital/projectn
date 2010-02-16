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
class logImport
{

    const POI = 'poi';
    const EVENT = 'event';
    const EVENT_OCCURRENCE = 'event_occurrence';
    const MOVIE = 'movie';

    /**
     * @var integer
     */
    public $totalInserts = 0;

    /**
     * @var integer
     */
    public $totalUpdates = 0;


    /**
     *
     * @var integer
     */
    public $totalErrors = 0;

    /**
     *
     * @var Object
     */
    public $vendorObj;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var Collection
     */
    public $errorsCollection;

    /**
     *
     * @var Collection
     */
    public $changesCollection;

    /**
     *
     * @var string
     */
    public $timer;

    /**
     *
     * Constructor
     *
     * @param int $vendorId
     * @param string Type of logger e.g. movie, poi, event
     */
    public function  __construct(Vendor $vendorObj, $type)
    {
        $this->vendorObj = $vendorObj;
        $this->checkType($type);
        $this->errorsCollection = new Doctrine_Collection(Doctrine::getTable('ImportLoggerError'));
        $this->changesCollection = new Doctrine_Collection(Doctrine::getTable('ImportLoggerChange'));
        $this->timer = sfTimerManager::getTimer('importTimer');
    }

 
    /**
     * Count each new insert
     */
    public function countNewInsert()
    {
        $this->totalInserts++;
    }

    /**
     * count each updated record
     */
    public function countUpdate()
    {
        $this->totalUpdates++;
    }

    /**
     * Save the stats
     */
    public function save()
    {
        $importObj = new ImportLogger;
        $importObj['total_inserts'] = $this->totalInserts;
        $importObj['total_updates'] = $this->totalUpdates;
        $importObj['type']          = $this->type;
        $importObj['total_errors']  = $this->totalErrors;
        $importObj['Vendor']        = $this->vendorObj;

        //Convertt he time to mysql format
        $totalTime = $this->timer->addTime();
        $timeStamp = $this->convertTime($totalTime);
  
        $importObj['total_time']    = $timeStamp;

        $importObj->save();

        //Save all errors
        foreach($this->errorsCollection as $error)
        {
            $error['ImportLogger'] = $importObj;
            $error->save();
        }

        //Save all changes
        foreach($this->changesCollection as $change)
        {
            $change['ImportLogger'] = $importObj;
            $change->save();
        }
       
    }

    /**
     *
     * Log the error
     *
     * @param Object $error
     * @param string $log
     *
     */
    public function addError(Exception $error, $log = '')
    {

        $errorObj               = new ImportLoggerError();
        $errorObj['trace']      = $error->__toString();
        $errorObj['log']        = $log;
        $errorObj['type']       = get_class($error);
        $errorObj['message']    = $error->getMessage();
        $this->errorsCollection[]    = $errorObj;

        //Increment the error count
        $this->totalErrors++;

    }

    /**
     * Log a change
     *
     * @param string $type
     * @param string $log Log of all updates
     */
    public function addChange($type, $log)
    {
        $changeObj = new ImportLoggerChange();
        $changeObj['log'] = $log;
        $changeObj['type'] = $type;

        $this->changesCollection[] = $changeObj;

    }


    /**
     * Check the type going in
     *
     * @param <string> $type
     */
    public function checkType($type)
    {
        $availableTypes = array( logImport::POI, logImport::EVENT, logImport::MOVIE );

        if( !in_array( $type, $availableTypes ) )
        {
            throw new Exception('Incorrect Type. Must be on of: ' . implode( ',', $availableTypes ) );
        }

        $this->type = $type;

    }


    public function convertTime($time)
    {
      $time = round($time, 0);

      if(is_numeric($time)){
        $value = array(
          "years" => 0, "days" => 0, "hours" => 0,
          "minutes" => 0, "seconds" => 0,
        );
        if($time >= 31556926){
          $value["years"] = floor($time/31556926);
          $time = ($time%31556926);
        }
        if($time >= 86400){
          $value["days"] = floor($time/86400);
          $time = ($time%86400);
        }
        if($time >= 3600){
          $value["hours"] = floor($time/3600);
          $time = ($time%3600);
        }
        if($time >= 60){
          $value["minutes"] = floor($time/60);
          $time = ($time%60);
        }
        $value["seconds"] = floor($time);

        $convertedTime =  (array) $value;

        $timeStamp = mktime($convertedTime['hours'], $convertedTime['minutes'], $convertedTime['seconds'], date('d'), date('m'), date('Y'));

        $timeStamp = date('H:i:s', $timeStamp);

        return $timeStamp;
      }else{
        throw Exception('Incorrect Timer');
      }
    }

}
?>
