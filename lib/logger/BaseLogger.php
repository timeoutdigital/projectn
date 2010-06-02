<?php
/*
 * BaseLogger
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
 */

abstract class BaseLogger
{

    public function  __construct()
    {
        $this->_timer = new sfTimer( 'loggerTimer' );
        $this->_timer->startTimer();
    }


    /**
     *
     * @var string
     */
    protected $_timer;


    
    /**
     *
     * @return time
     */
    protected function _getElapsedTime()
    {
        $this->_timer->addTime();
        $this->_timer->startTimer();
        $seconds = $this->_timer->getElapsedTime();

        $timeStamp = mktime( 0, 0, $seconds );

        return date('H:i:s', $timeStamp);
    }
}

?>