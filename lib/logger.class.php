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
 */
class loggerclass {

    /**
     * @var integer
     */
    public $totalInserts = 0;

    /**
     * @var integer
     */
    public $totalUpdates = 0;


    public function __constructor()
    {   
    }

    /**
     * Count each new insert
     */
    public function countNewInsert()
    {
        $this->totalUpdates++;
    }

    /**
     * count each updated record
     */
    public function countUpdate()
    {
        $this->totalInserts++;
    }
}
?>
