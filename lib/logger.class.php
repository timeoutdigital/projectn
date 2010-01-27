<?php
/* 
 * Logger class to log inserts and updates.
 */

/**
 * Description of loggerclass
 *
 * @package projectn
 * @subpackage lib
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
class logger {

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
     * @var Object
     */
    public $vendorObj;

    /**
     *
     * @var string
     */
    public $type;

    /**
     * Constructor
     *
     * @param int $vendorId
     * @param string Type of logger e.g. movie, poi, event
     */
    public function  __construct($vendorObj, $type)
    {
        $this->vendorObj = $vendorObj;
        $this->checkType($type);
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
    public function saveStats()
    {
        $statsObj = new ImportStats;
        $statsObj['total_inserts'] = $this->totalInserts;
        $statsObj['total_updates'] = $this->totalUpdates;
        $statsObj['type'] = $this->type;
        $statsObj['Vendor'] = $this->vendorObj;
        $statsObj->save();
    }

    /**
     * Check the type going in
     *
     * @param <string> $type
     */
    public function checkType($type)
    {

        if($type != 'movie' && $type != 'poi' && $type != 'event')
        {
            throw new Exception('Incorrect Type. Must be on of: "movie" "poi" "event"');
        }

        $this->type = $type;

    }
}
?>