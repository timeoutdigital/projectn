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

    /**
     * Pass optional arguments passed to task
     * @param array $options
     */
    public function  __construct( $options = array() ) {

        // set options
        $this->options = is_array( $options ) ? $options : array();
        $this->errors = array();
    }

    public function getErrors()
    {
        // Load Vendors
        $this->vendors = Doctrine::getTable( 'Vendor' )->findAll( 'KeyValue' );

        // get Yaml file location
        $configFile = ( !isset( $this->options['yml'] ) || $this->options['yml'] == null ) ? sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'importBoundaries.yml' : $this->options['yml'];

        // check YAML file exists
        if( !file_exists( $configFile ) )
        {
            throw new ImportBoundariesCheckException( "YAML file not found, at {$configFile}" );
        }

        // Load the YML config File and Parse it into Config
        $config = sfYaml::load( $configFile );
        $this->parseConfig( $config );

        $todayDate = date( 'Y-m-d' );
        $yesterdayDate = date( 'Y-m-d', strtotime( '-1 day' ) );

        // Evaluate
        // Check for Boundaries
        foreach ( $this->vendors as $vendorID => $city )
        {
            $cityName = str_replace( ' ' , '_', $city ); // CityName

            if( in_array( $cityName, $this->excludedVendors ) )
                continue;

            // Date Range for log Count Query
            $dateFrom   = date( 'Y-m-d', strtotime( '-1 day' ) ); // yesterday
            $dateTo     = date( 'Y-m-d', strtotime( '+1 day' ) ) ; // to Tomorrow

            $todayImportLog = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $vendorID, $dateFrom, $dateTo, Doctrine_Core::HYDRATE_ARRAY );

            if( !isset($todayImportLog) || empty($todayImportLog) )
            {
                $this->addError( sprintf( 'No Import Log found for City "%s" Today ( Checked at : %s)', $city, date('d M Y H:i:s ') ) );
                continue;
            }

            // Extract Stats and evaluate
            $stats = $this->extractStats( $todayImportLog ); // Extract the Date

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

                    if( $droppedPercent > $values['threshold'] )
                    {
                        $this->addError( str_pad( ucfirst($city) , 20 ) . ": " . str_pad( ucfirst($model) , 15)." | import count dropped by {$droppedPercent}% ({$droppedAmount}), threshold: {$values['threshold']}%" );
                    }
                }
            } // foreach city > model
        }

        // return error messages
        return $this->errors;
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
     * Parse Loaded YML file into config with All vendors and Overrides as specified in YML file
     * @param Array $ymlData
     */
    private function parseConfig( $ymlData )
    {
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
    protected function extractStats( array $array )
    {
        $metrics = array( 'insert' => 0, 'failed' => 0, 'updated' => 0, 'existing' => 0 );
        $dates = array();

        foreach( $array as $logImport )
        {
            $date = date( 'Y-m-d', strtotime( $logImport['created_at'] ) );

            if( !array_key_exists( $date, $dates ) )
                $dates[ $date ] = array( 'poi' => $metrics, 'event' => $metrics, 'movie' => $metrics, 'eventoccurrence' => $metrics );

            foreach( $logImport['LogImportCount'] as $logImportCount )
                $dates[ $date ][ strtolower( $logImportCount['model'] ) ][ $logImportCount['operation'] ] += $logImportCount['count'];

            // Validate Sucess
            if( $date == date('Y-m-d')  && 'success' != strtolower( $logImport['status'] ))
            {
                $this->addError( str_pad( ucfirst( $this->vendors[ $logImport['vendor_id'] ] ), 20 ) . ": " . str_pad( "Task {$logImport['id']}", 15 )." | failed to complete." );
            }
        }

        return $dates;
    }
}

class ImportBoundariesCheckException extends Exception
{

}