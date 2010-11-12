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
        $startDate  = strtotime("-120 days");
        $endDate    = time();

        $this->graphData = array();

        foreach( Doctrine::getTable('Vendor')->findAll() as $vendor )
        {
            $this->graphData[ $vendor['city'] ] = array();
            
            for( $timestamp=$startDate+86400; $timestamp<=$endDate; $timestamp+=86400 )
            {
                $this->graphData[ $vendor['city'] ][ date( 'Y-m-d', $timestamp ) ] = array( 'Poi' => 0, 'Event' => 0, 'Movie' => 0 );
            }
        }

        $logs = Doctrine::getTable('LogExport')
            ->createQuery('l')
                ->leftJoin('l.Vendor v ON l.vendor_id = v.id')
                ->leftJoin('l.LogExportCount c ON l.id = c.log_export_id')
            ->where('UNIX_TIMESTAMP( l.created_at ) BETWEEN ? AND ?', array( $startDate, $endDate ) )
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        foreach( $logs as $log )
        {
            $logDate    = date( 'Y-m-d', strtotime( $log['created_at'] ) );
            $logVendor  = $log['Vendor']['city'];
            
            foreach( $log['LogExportCount'] as $logCount )
            {
                $this->graphData[ $logVendor ][ $logDate ][ $logCount['model'] ] = $logCount['count'];
            }
        }
    }
}
