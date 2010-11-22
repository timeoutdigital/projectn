<?php

/**
 * Check that the database does not contain duplicate categories.
 *
 * @package projectn
 * @subpackage task
 *
 * @author Peter Johnson
 * @email peterjohnson@timeout.com
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */

class duplicateCategoriesCheckTask extends nagiosTask
{
    /**
     * Set Database connection to true
     * @var boolean
     */
    protected $enableDB     = true;
    
    /**
     * Set String description for this TASK
     * @var string
     */
    protected $description  = 'Check that the database does not contain duplicate categories';
    
    /**
     * Impliments abstract method, Core Nagios Check method.
     * @param array $arguments
     * @param array $options
     */
    protected function executeNagiosTask( $arguments = array(), $options = array() )
    {
        // Poi Categories
        $q = Doctrine::getTable( 'VendorPoiCategory' )
            ->createQuery()
            ->select('name as name, count(*) as c')
            ->groupBy('name')
            ->having('count(*) > 1')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalDuplicatePoiCategories = (int) count( $q );
        
        if( $totalDuplicatePoiCategories > 0 )
        {
            $this->addError( "Database contains {$totalDuplicatePoiCategories} duplicate poi categories." );
        }

        // Event Categories
        $q = Doctrine::getTable( 'VendorEventCategory' )
            ->createQuery()
            ->select('name as name, count(*) as c')
            ->groupBy('name')
            ->having('count(*) > 1')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalDuplicateEventCategories = (int) count( $q );

        if( $totalDuplicateEventCategories > 0 )
        {
            $this->addError( "Database contains {$totalDuplicateEventCategories} duplicate event categories." );
        }

    }
}