<?php
/*
 * ExportLogger
 *
 * @package projectn
 * @subpackage logger.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * <b>Example</b>
 * <code>
 * //start the logger
 * ExportLogger::getInstance()->setVendor( $this->vendor )->start();
 *
 * //add an export count
 * ExportLogger::getInstance()->addExport( 'Poi' );
 *
 * //add an error
 * ExportLogger::getInstance()->addError( 'test message', 'Poi', '1' );
 *
 * //end the logger
 * ExportLogger::getInstance()->end();
 *
 * //get Totals (by model if required)
 * ExportLogger::getInstance()->getTotal();
 * ExportLogger::getInstance()->getTotal( 'Poi' );
 *
 * //get Error Totals (by model if required)
 * ExportLogger::getInstance()->getTotalError();
 * ExportLogger::getInstance()->getTotalError( 'Poi' );
 * </code>
 *
 */

class ExportLogger extends BaseLogger
{
    /**
     *
     * @var Vendor
     */
    private $_vendorObj;

    /**
     *
     * @var ExportLogger
     */
    private $_exportLog;

    /**
     *
     * @var instance
     */
    private static $_instance;


    /**
     * Constructor (protected)
     */
    protected function  __construct()
    {
        parent::__construct();
    }

    /**
     *  The singleton method
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance))
        {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**
     * start the logger
     */
    public function start()
    {
        $this->_exportLog = new LogExport;
        $this->_exportLog['Vendor']         = $this->_vendorObj;
        $this->_exportLog['status']         = 'running';
        $this->_exportLog['total_time']     = '00:00';
        $this->_exportLog->save();
    }

    /**
     * end the logger
     */
    public function end()
    {
        $this->_exportLog['status']         = 'success';
        $this->save();
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
     * @param string $limitByModel
     * @return integer
     */
    public function getTotal( $limitByModel = '' )
    {
        $counter = 0;

        foreach( $this->_exportLog[ 'LogExportCount' ] as $logExportCount )
        {
            if ( $limitByModel == '' || $limitByModel == $logExportCount[ 'model' ] )
            {
                $counter += $logExportCount[ 'count' ];

                if ( $limitByModel == $logExportCount[ 'model' ] )
                    break;
            }
        }

        return $counter;
    }

    /**
     *
     * @param string $limitByModel
     * @return integer
     */
    public function getTotalError( $limitByModel = '' )
    {
        $counter = 0;

        foreach( $this->_exportLog[ 'LogExportError' ] as $logExportError )
        {
            if ( $limitByModel == '' || $limitByModel == $logExportError[ 'model' ] )
                $counter++;
        }

        return $counter;
    }

    /**
     *
     * Add an error to be logged.
     *
     * @param string $message Any extra details that can help someone solve this error
     * @param Doctrine_Record $record The record causeing the error
     *
     */
    public function addError( $message, $model = '', $id = '' )
    {

        $exportRecordErrorLogger                     = new LogExportError();
        $exportRecordErrorLogger['log']              = $message;

        if ( $model != '' )
           $exportRecordErrorLogger['model']         = $model;

        if ( $id != '' )
           $exportRecordErrorLogger['record_id']     = $id;

        $this->_exportLog[ 'LogExportError' ][] = $exportRecordErrorLogger;
        $this->save( );
    }

    /**
     * Record a datestamp for Export
     *
     * @param string $model
     * @param string $recordId
     *
     * This function records a datestamp for an export.
     *
     */
    public function addDatestamp( $model, $recordId )
    {
        try {
            // Create a new LogExportDate object.
            $d = new LogExportDate();
            $d['model'] = $model;
            $d['record_id'] = $recordId;
            $d['export_date'] = date("Y-m-d H:i:s");
            $d->save( );
        }
        catch( Exception $e )
        {
            // Record Save Errors in Database.
            $this->addError( 'Failed to save datestamp for export', $model, $recordId );
        }

        // Clean Up.
        if( method_exists( $d, 'free' ) ) $d->free( true );
        if( isset( $d ) ) unset( $d );
    }

    /**
     * Initalize an Export
     *
     * @param string $model
     *
     * This function initializes a the export count to 0.
     *
     */
    public function initExport( $model )
    {
        $logExportCount = new LogExportCount();
        $logExportCount[ 'model' ] = $model;
        $logExportCount[ 'count' ] = 0;

        $this->_exportLog[ 'LogExportCount' ][] = $logExportCount;
        $this->save( );
    }

    /**
     * Log an export
     *
     * @param string $model
     * @param string $recordId
     *
     * This function can deal with different types of models (thats what the
     * loop is for). However at the moment we are not using this. It is still
     * kept like that for the future as well its implemented similar on the
     * import logger.
     *
     */
    public function addExport( $model, $recordId )
    {
        if ( $this->_exportLog[ 'LogExportCount' ] instanceof Doctrine_Collection )
        {
            foreach( $this->_exportLog[ 'LogExportCount' ] as $k => $logExportCount )
            {
                if ( $this->_exportLog[ 'LogExportCount' ][ $k ][ 'model' ] == $model  )
                {
                    $this->_exportLog[ 'LogExportCount' ][ $k ][ 'count' ] += 1;
                    $this->save( );

                    $this->addDatestamp( $model, $recordId );
                    return;
                }
            }
        }

        $this->initExport( $model ); // Initalize a new export count to 0.
        $this->addExport( $model, $recordId ); // Recurse to add Export.
    }

    /**
     * Save the stats
     */
    private function save()
    {
        $this->_exportLog['total_time'] = $this->_getElapsedTime();
        $this->_exportLog->save();
    }

    /**
     * Outputs the export errors
     *
     */
    public function showErrors()
    {
        foreach ($this->_exportLog[ 'LogExportError' ] as $logExportError)
        {
            echo "EXPORT : " . $logExportError[ 'model' ] . ' : ' . $logExportError[ 'log' ] .PHP_EOL;
        }
    }
}

?>