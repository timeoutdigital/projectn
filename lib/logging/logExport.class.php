<?php
/* 
 * Logger class to log exported items.
 */

/**
 * Description of logExport class
 *
 * @package projectn
 * @subpackage logging.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 *
 * // create a new logExport instance
 * $logExportObj = new logExport( $vendorObj[ 'id' ], 'poi' );
 *
 * // add a an item ( our uid, vendors uid)
 * $logExportObj->addItem( 1, 'some id');
 *
 * </code>
 *
 */
class logExport
{

    /**
     *
     * @var ExportLogger
     */
    private $_exportLogger;

    /**
     *
     * @var sfTimer
     */
    private $_timer;


    
    public function  __construct( $vendorId, $model )
    {
        $this->_exportLogger = new ExportLogger();

        $this->_exportLogger[ 'vendor_id' ] = $vendorId;
        $this->_exportLogger[ 'model' ] = $model;
        $this->_exportLogger[ 'total_time' ] = 0;
        $this->_exportLogger->save();
        
        $this->_timer = new sfTimer( 'logExportTimer' );
        $this->_timer->startTimer();
    }

    public function addItem( $ItemId, $vendorItemId )
    {
        $exportLoggerItem = new ExportLoggerItem();
        $exportLoggerItem[ 'item_id' ] = $ItemId;
        $exportLoggerItem[ 'vendor_item_id' ] = $vendorItemId;
        
        $this->_exportLogger[ 'ExportLoggerItem' ][] = $exportLoggerItem;
    }

    public function save()
    {
        $this->_exportLogger[ 'total_time' ] = $this->_getElapsedTime();
        $this->_exportLogger->save();
    }

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
