<?php

/**
 * dashboard actions.
 *
 * @package    sf_sandbox
 * @subpackage dashboard
 * @author     Peter Johnson
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class exportstatsActions extends sfActions
{
    public function executeIndex( sfWebRequest $request)
    {
        $this->form = new ExportStatsDateRangeSelectionForm;
        $this->a2zVendors = Doctrine::getTable( 'Vendor' )->findAllVendorsInAlphabeticalOrder( Doctrine_Core::HYDRATE_ARRAY );
    }

    public function  executeGraph( sfWebRequest $request )
    {
        $startDate  = date( 'Y-m-d', strtotime("-1 month") );
        $endDate    = date( 'Y-m-d' );

        // Extract dates from request and generate UNIX time stamp
        if( is_numeric( $request->getPostParameter('date_from_month') ) )
        {
            // generate as yyyy/mm/dd HH:ii:ss
            $startDate = $request->getPostParameter('date_from_year') . '-' .
                    $request->getPostParameter('date_from_month') . '-' .
                    $request->getPostParameter('date_from_day') ;
        }

        if( is_numeric( $request->getPostParameter('date_to_month') ) )
        {
            $endDate =  $request->getPostParameter('date_to_year') . '-' .
                    $request->getPostParameter('date_to_month') . '-' .
                    $request->getPostParameter('date_to_day');
        }

        $this->vendor = Doctrine::getTable( 'Vendor' )->find( $request->getPostParameter( 'vendor_id' ) );

        if( $this->vendor === false )
        {
            throw new Exception( 'Invalid vendor ID' );
        }
        
        // Init empty graph.
        $this->graphData = array();
        for( $timestamp=strtotime($startDate); $timestamp<=strtotime($endDate); $timestamp+=86400 )
        {
            $this->graphData[ date( 'Y-m-d', $timestamp ) ] = array( 'Poi' => 0, 'Event' => 0, 'Movie' => 0 );
        }
        
        // get logs by date / vendor :: End date need to adjusted by +1 for between to match with DATE+TIME
        $logs = Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecords( $this->vendor['id'],  $startDate, date('Y-m-d', ( strtotime($endDate) + 86400) ), Doctrine_Core::HYDRATE_ARRAY );
        
        // Convert to graph format
        foreach( $logs as $log )
        {
            $logDate    = date( 'Y-m-d', strtotime( $log['created_at'] ) );
            
            foreach( $log['LogExportCount'] as $logCount )
            {
                $this->graphData[ $logDate ][ $logCount['model'] ] = $logCount['count'];
            }
        }

    }
}
