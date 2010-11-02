<?php

/**
 * check for import iteration
 * This class is written tobe used by Task, this can be later extended to use with View Model
 *
 * @package projectn
 * @subpackage task
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class importBoundariesCheck
{
    /**
     * Options passed through constructor
     * @var array
     */
    private $options;
    /**
     * Holds the minimum and threashold value for Each City -> Model -> minimum/threashold
     * @var array
     */
    protected $config;

    protected $vendors;

    protected $excludedVendors;

    protected $errors;

    protected $percentageDiffByDays;

    const IMPORT = 'import';
    const EXPORT = 'export';


    /**
     * Pass optional arguments passed to task
     * @param array $options
     */
    public function  __construct( $options = array() ) {

        // set options
        $this->options = is_array( $options ) ? $options : array();
        $this->errors = array();

        // Load Vendors
        $this->vendors = Doctrine::getTable( 'Vendor' )->findAllVendorsInAlphabeticalOrder( 'KeyValue' );

        // Load config file
        $this->loadAndParseConfig( );

        if( isset( $options['daysToAnalyse'] ) && is_numeric( $options['daysToAnalyse'] ) )
        {
            if( isset( $options['type'] ) && $options['type'] === self::IMPORT )
            {
                $this->getPercentageDiffByXDaysForImport( $options['daysToAnalyse'] );
            }
            else if( isset( $options['type'] ) && $options['type'] === self::EXPORT )
            {
                $this->getPercentageDiffByXDaysForExport( $options['daysToAnalyse'] );
            }
        }
    }

    /**
     * Get Import log and count threshold variant for given date range
     * @param int $days
     * @param mix $filterVendorID
     * @return mix
     */
    public function getPercentageDiffByXDaysForImport( $days, $filterVendorID = null )
    {
        if( $days == null || !is_numeric($days) || $days <= 0)
        {
            throw new ImportBoundariesCheckException( 'getPercentageDiffByXDaysForImport invalid $days, Days should be positive integer value' );
        }

        if( $filterVendorID !== null && !isset( $this->vendors[$filterVendorID] ) )
        {
            throw new ImportBoundariesCheckException( 'getPercentageDiffByXDaysForImport vendor not found' );
        }

        $this->percentageDiffByDays = null; // reset
        // Return Results
        $this->percentageDiffByDays = $this->getPercentageDiffByXDays( self::IMPORT, $days, $filterVendorID );
    }

    /**
     * Get Export log and count threshold variant for given date range
     * @param int $days
     * @param mix $filterVendorID
     * @return mix
     */
    public function getPercentageDiffByXDaysForExport( $days, $filterVendorID = null )
    {
        if( $days == null || !is_numeric($days) || $days <= 0)
        {
            throw new ImportBoundariesCheckException( 'getPercentageDiffByXDaysForExport invalid $days, Days should be positive integer value' );
        }

        if( $filterVendorID !== null && !isset( $this->vendors[$filterVendorID] ) )
        {
            throw new ImportBoundariesCheckException( 'getPercentageDiffByXDaysForExport vendor not found' );
        }

        $this->percentageDiffByDays = null; // reset
        
        // Return Results
        $this->percentageDiffByDays = $this->getPercentageDiffByXDays( self::EXPORT, $days, $filterVendorID );
    }

    /**
     * Private function to query IMPORT or EXPORT
     * @param IMPORT/EXPORT $TYPE
     * @param int $days
     * @param int $filterVendorID
     * @return mix
     */
    private function getPercentageDiffByXDays( $TYPE, $days, $filterVendorID = null )
    {
        if( $TYPE !== self::IMPORT && $TYPE !== self::EXPORT )
        {
            throw new ImportBoundariesCheckException( 'getPercentageDiffByXDays - Invalid $TYPE' );
        }
        
        // Get the Import Log and Filter out by Dates
        $backDateNumber = intval( ( $days * 2 ) - 1 );
        $todayDate = date( 'Y-m-d', strtotime( '+1 day') );
        $firstDate = date( 'Y-m-d', strtotime( "-{$backDateNumber} day" ) );

        // Query Database now and get All vendors Results, unless filtervendorID specified
        $logResultSet = null;
        if( $filterVendorID == null )
        {
            $logResultSet = ( $TYPE === self::IMPORT ) ? Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByDates( $firstDate, $todayDate, Doctrine_Core::HYDRATE_ARRAY ) :
                    Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecordsByDates( $firstDate, $todayDate, Doctrine_Core::HYDRATE_ARRAY );
        }
        else
        {
            $logResultSet = ( $TYPE === self::IMPORT ) ? Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $filterVendorID, $firstDate, $todayDate, Doctrine_Core::HYDRATE_ARRAY ) :
                    Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecords( $filterVendorID, $firstDate, $todayDate, Doctrine_Core::HYDRATE_ARRAY );
        }

        if( !is_array( $logResultSet ) || empty( $logResultSet ) )
        {
            throw new ImportBoundariesCheckException( 'query return empty log records...' );
        }

        // Extract the Log into easy to use Array
        $logExtracted = ( $TYPE === self::IMPORT ) ? $this->extractImportStats( $logResultSet ) : $this->extractExportStats( $logResultSet );

        // Store changes
        $changesArray = array();

        // Loop through vendor
        foreach( $this->vendors as $vendorID => $city )
        {
            $cityName = str_replace( ' ' , '_', $city );

            // Skip cities marked as exclude in YAML file
            if( in_array( $cityName , $this->excludedVendors ) )
            {
                continue;
            }
            
            if( isset($vendorID) && is_numeric( $filterVendorID ) && $filterVendorID != $vendorID)
            {
                continue; // Requested Only 1 vendor
            }

            // get Vendor Specific Logs
            $logCountByDate = isset($logExtracted[$vendorID]) ? $logExtracted[$vendorID] : null;

            // Check to confirm we have All required Log
            if(!is_array($logCountByDate) || count($logCountByDate) != ( $backDateNumber + 1 ) || ( count($logCountByDate) % $days ) != 0 )
            {
                $this->addError( "{$TYPE} : {$city} - invalid number of logs found, unable to process any further. Total Number of Records found: " . count($logCountByDate) );
                continue;
            }

            // split result
            $logChunk = array_chunk( $logCountByDate, $days, true );

            // Add them Up
            $defaults = array( 'poi' => 0, 'event' => 0, 'movie' => 0 );
            $sumOfArrays = array( $defaults, $defaults ); // Two set of arrasy required as we have to calculate lastweek, this week and so on...

            foreach( $logChunk as $key => $modelValue )
            {
                foreach( $modelValue as $dateCount )
                {
                    foreach( $dateCount as $model => $ieCount )
                    {
                        if( $TYPE === self::IMPORT )
                        {
                            // Import logs $ieCount is an array with seperate values for Inser, udpate, existing etc... uisng array_sum will add each of those values
                            $sumOfArrays[ $key ][ strtolower( $model) ] += array_sum( $ieCount);
                        }
                        else
                        {
                            // Export Don't have Insert, update etc... hence, we only need to add the value
                            $sumOfArrays[ $key ][ strtolower( $model) ] += $ieCount;
                        }
                    }
                }
            }

            // calculate the Differences in percentage
            foreach( $sumOfArrays[ 0 ] as $model => $pastPeriodTotalCount )
            {
                // Get current period count
                $currentPeriodTotalCount = $sumOfArrays[ 1 ][ $model ];

                if( $pastPeriodTotalCount == 0 || $currentPeriodTotalCount == 0 )
                {
                    // Error Devided by Zero!
                    $this->addError( "{$TYPE} : Unable to calculate variant for {$city}::{$model}, devide or by 0 [ First Value: {$pastPeriodTotalCount}, Second Value: {$currentPeriodTotalCount}]." );
                    continue;
                }
                // calculate variant
                //$calculatedPercentage = ( ( ($currentPeriodTotalCount / $days ) ) / ( ( $pastPeriodTotalCount / $days ) ) );
                $calculatedPercentage = ( ( $currentPeriodTotalCount  ) / ( $pastPeriodTotalCount ) );
                $calculatedPercentage =  ( (  $calculatedPercentage * 100 ) - 100 ); // Get change Percentage

                $calculatedNumber = ( $currentPeriodTotalCount - $pastPeriodTotalCount ); // Get the difference

                // get Status
                $status = 'good';
                $lowerThresHold = $this->getLowerThresholdFor( $cityName, $model ) ;
                $upperThresHold = $this->getUpperThresholdFor( $cityName, $model ) ;
                
                if( $calculatedPercentage == 0 )
                {
                    $status = 'ok';
                }
                else if( $lowerThresHold !== null && $calculatedPercentage < $lowerThresHold)
                {
                    $status = 'verybad';
                }
                else if( $upperThresHold !== null && $calculatedPercentage > $upperThresHold)
                {
                    $status = 'verygood';
                }
                else if( $calculatedPercentage < 0 )
                {
                    $status = 'bad';
                }

                $changesArray[ $cityName ][ $model ] = array(
                                                    'percentage' => $calculatedPercentage,
                                                    'number' => $calculatedNumber,
                                                    'status' => $status,
                                                    'pastPeriodCount' => $pastPeriodTotalCount,
                                                    'currentPeriodCount' => $currentPeriodTotalCount,
                                                    );
            }

        }// $vendor
        
        return $changesArray;
    }

    
    public function processImportLog()
    {
        $todayDate = date( 'Y-m-d' );
        $yesterdayDate = date( 'Y-m-d', strtotime( '-1 day' ) );
        // Date Range for log Count Query
        $dateFrom   = date( 'Y-m-d', strtotime( '-1 day' ) ); // yesterday
        $dateTo     = date( 'Y-m-d', strtotime( '+1 day' ) ) ; // to Tomorrow
        
        // Query the Database Only Once, and it will Return Log for all vendors.
        // Extract function will conver it into easy to use Array( vendor_id => array( date => ...) )
        $todayImportLog = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByDates( $dateFrom, $dateTo, Doctrine_Core::HYDRATE_ARRAY );

        // Extract Stats and evaluate
        $importLog = $this->extractImportStats( $todayImportLog ); // Extract the Date
        
        // Check for Boundaries
        foreach ( $this->vendors as $vendorID => $city )
        {
            $cityName = str_replace( ' ' , '_', $city ); // CityName

            // Skip Cities when specified in exclude list
            if( in_array( $cityName, $this->excludedVendors ) )
                continue;

            // Check Log Exists
            if( !isset($importLog[$vendorID]) || empty($importLog[$vendorID]) )
            {
                $this->addError( sprintf( 'No Import Log found for City "%s" Today ( Checked at : %s)', $city, date('d M Y H:i:s ') ) );
                continue;
            }

            $stats = $importLog[$vendorID]; // get this vendor Logs

            foreach( $this->config[ $cityName ] as $model => $values )
            {
                // check for Log existing
                if( !array_key_exists( $todayDate, $stats ) )
                {
                    $this->addError( str_pad( ucfirst($city), 20 ) . ": " . str_pad( ucfirst( $model ) , 15 ) . " | Error: No Import log found for date {$todayDate}" );
                    continue;
                }
                // Check for Minimum Boundary
                $todayIteration = ( $stats[$todayDate][ $model ][ 'insert' ] + $stats[$todayDate][ $model ][ 'failed' ] + $stats[$todayDate][ $model ][ 'updated' ] + $stats[$todayDate][ $model ][ 'existing' ] );
                if( $todayIteration < $values['minimum'] )
                {
                    $this->addError( str_pad( ucfirst($city), 20 ) . ": " . str_pad( ucfirst($model) , 15 )." | fell behind the minimum iteration count (Actual: {$todayIteration} Expected: {$values['minimum']})");
                } else {

                    // check for Log existing
                    if( !array_key_exists( $yesterdayDate, $stats ) )
                    {
                        $this->addError( str_pad( ucfirst($city), 20 ) . ": " . str_pad(ucfirst( $model ) , 15 ) . " | Error: No Import log found for date {$yesterdayDate} to calculate drop percentage" );
                        continue;
                    }

                    $yesterDaylIteration = $stats[$yesterdayDate][ $model ][ 'insert' ] + $stats[$yesterdayDate][ $model ][ 'failed' ] + $stats[$yesterdayDate][ $model ][ 'updated' ] + $stats[$yesterdayDate][ $model ][ 'existing' ];

                    if( $yesterDaylIteration == 0 || $todayIteration == 0 )
                    {
                        $this->addError( str_pad( ucfirst($city), 20 ) . ": " . str_pad( ucfirst($model) , 15 )." | unable to calculate dropped percentage, devided by Zero (Yesterdays iteration: {$yesterDaylIteration}  | Todays iteration: {$todayIteration})" );
                        continue;
                    }

                    $droppedPercent = ( 100 - round( ( $todayIteration / $yesterDaylIteration )  * 100 ) );
                    $droppedAmount = $yesterDaylIteration - $todayIteration;

                    if( $droppedPercent > $values['lower_threshold'] )
                    {
                        $this->addError( str_pad( ucfirst($city) , 20 ) . ": " . str_pad( ucfirst($model) , 15)." | import count dropped by {$droppedPercent}% ({$droppedAmount}), lower threshold: {$values['lower_threshold']}%" );
                    }
                }
            } // foreach city > model
        }
    }
    
    public function getErrors()
    {
        // return error messages
        return $this->errors;
    }

    
    public function getLowerThresholdFor( $cityName, $modelName )
    {
        if( !isset( $this->config[ $cityName ][ $modelName ][ 'lower_threshold' ]  ) )
        {
            return null;
        }

        return $this->config[ $cityName ][ $modelName ][ 'lower_threshold' ];
    }

    public function getUpperThresholdFor( $cityName, $modelName )
    {
        if( !isset( $this->config[ $cityName ][ $modelName ][ 'upper_threshold' ]  ) )
        {
            return null;
        }

        return $this->config[ $cityName ][ $modelName ][ 'upper_threshold' ];
    }

    /**
     * Methods to access Import/Export percentages
     */

    /**
     * get the percentage variant by Cityname and Model Name
     * @param string $cityName
     * @param string $modelName
     * @return mix
     */
    public function getVariantPercentageBy( $cityName, $modelName, $decimalPlaces = null )
    {
        $value = isset( $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'percentage' ] ) ? $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'percentage' ] : null;
        if( is_int( $decimalPlaces ) && is_numeric( $value ) )
        {
            return round( $value, $decimalPlaces );
        }

        return $value;
    }

    /**
     * get the number variant by Cityname and Model Name
     * @param string $cityName
     * @param string $modelName
     * @return mix
     */
    public function getVariantNumberBy( $cityName, $modelName )
    {
        return isset( $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'number' ] ) ? $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'number' ] : null;
    }

    /**
     * get the status by comparing to confic YAML file threshold limit
     * @param string $cityName
     * @param string $modelName
     * @return string error, warning, ok
     */
    public function getStatusBy( $cityName, $modelName )
    {

        return isset( $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'status' ] ) ?  $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'status' ] : null;
    }

    public function getPastPeriodCountBy( $cityName, $modelName )
    {
        return isset( $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'pastPeriodCount' ] ) ? $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'pastPeriodCount' ] : null ;
    }

    public function getCurrentPeriodCountBy( $cityName, $modelName )
    {
        return isset( $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'currentPeriodCount' ] ) ? $this->percentageDiffByDays[ $cityName ][ $modelName ][ 'currentPeriodCount' ] : null ;
    }

    public function getIncludedCities( )
    {
        return array_keys( $this->percentageDiffByDays );
        
    }

    /**
     * Helper function to store error messages
     * @param string $message
     */
    private function addError( $message )
    {
        $this->errors[] = $message;
    }

    /**
     * Load Config YMAL file and
     * Parse Loaded YML file into config with All vendors and Overrides as specified in YML file
     */
    private function loadAndParseConfig( )
    {
        // get Yaml file location
        $configFile = ( !isset( $this->options['yml'] ) || $this->options['yml'] == null ) ? sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'importBoundaries.yml' : $this->options['yml'];

        // check YAML file exists
        if( !file_exists( $configFile ) )
        {
            throw new ImportBoundariesCheckException( "YAML file not found, at {$configFile}" );
        }

        // Load the YML config File and Parse it into Config
        $ymlData = sfYaml::load( $configFile );
        
        // Reset Variables
        $this->config = array();
        $this->excludedVendors = array();

        // Set Excluded vendors
        $this->excludedVendors = $ymlData['exclude'];

        // Set Defaults + Overriders
        foreach( $this->vendors as $city )
        {
            $cityName = str_replace( ' ', '_', $city );

            // Skip When Excluded vendors Found
            if( in_array( $cityName, $this->excludedVendors ) )
                continue;

            // Set Default Data
            if( !isset( $this->config[ $cityName ] ) )
                $this->config[ $cityName ] = $ymlData['default'];

            // Check for Override
            if( !isset( $ymlData[ $cityName ] ) )
                continue; // City Specific Boundaries NOT SET

            // Apply Overrides to Default values
            foreach ( $ymlData[ $cityName ] as $modelName => $modelValue )
            {
                // For each Model, Apply Values
                foreach ( $modelValue as $overrideKey => $overrideValue )
                    $this->config[ $cityName ][ $modelName ][ $overrideKey ] = $overrideValue;

            } //  $ymlData[ $cityName ] as $mode

        } // For each City
    }

    /**
     * Convert returned results into date -> model based array results set
     * @param array $array
     * @return array
     */
    protected function extractImportStats( array $array )
    {
        $operationDefinition = Doctrine::getTable('LogImportCount')->getColumnDefinition('operation');
        $availableOperations = $operationDefinition['values'];
        $metrics = array_fill_keys( array_values( $availableOperations ), 0 );
        $vendorsAndDates = array();

        foreach( $array as $logImport )
        {
            $date = date( 'Y-m-d', strtotime( $logImport['created_at'] ) );

            // Check vendor ID array Key Exists
            if( !array_key_exists( $logImport['vendor_id'], $vendorsAndDates ) )
                $vendorsAndDates[ $logImport['vendor_id'] ] = array();

            // check and create Date and Default Array
            if( !array_key_exists( $date, $vendorsAndDates[ $logImport['vendor_id'] ] ) )
                $vendorsAndDates[ $logImport['vendor_id'] ][ $date ] = array( 'poi' => $metrics, 'event' => $metrics, 'movie' => $metrics );

            foreach( $logImport['LogImportCount'] as $logImportCount )
            {
                if( strtolower( $logImportCount['model'] ) === 'eventoccurrence' )
                {
                    continue; // Skip event occurrence
                }

                $vendorsAndDates[ $logImport['vendor_id'] ][ $date ][ strtolower( $logImportCount['model'] ) ][ $logImportCount['operation'] ] += $logImportCount['count'];
            }

            // Validate Sucess
            if( $date == date('Y-m-d')  && 'success' != strtolower( $logImport['status'] ))
            {
                $this->addError( str_pad( ucfirst( $this->vendors[ $logImport['vendor_id'] ] ), 20 ) . ": " . str_pad( "Task {$logImport['id']}", 15 )." | failed to complete." );
            }
        }

        return $vendorsAndDates;
    }

    /**
     * Convert returned results into date -> model based array results set
     * @param array $array
     * @return array
     */
    protected function extractExportStats( array $array )
    {
        $vendorsAndDates = array();

        foreach( $array as $logExport )
        {
            // Check vendor ID array Key Exists
            if( !array_key_exists( $logExport['vendor_id'], $vendorsAndDates ) )
                $vendorsAndDates[ $logExport['vendor_id'] ] = array();

            $date = date( 'Y-m-d', strtotime( $logExport['created_at'] ) );
            
            if( !array_key_exists( $date, $vendorsAndDates[ $logExport['vendor_id'] ] ) )
                $vendorsAndDates[ $logExport['vendor_id'] ][ $date ] = array( 'poi' => 0, 'event' => 0, 'movie' => 0);

            foreach( $logExport['LogExportCount'] as $logExportCount )
                $vendorsAndDates[ $logExport['vendor_id'] ][ $date ][ strtolower( $logExportCount['model'] ) ] += $logExportCount['count'];
        }

        return $vendorsAndDates;
    }
}

class ImportBoundariesCheckException extends Exception
{

}