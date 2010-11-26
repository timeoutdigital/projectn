<?php

/**
 * Check that the database does not contain invalid categories.
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

class categoriesCheckTask extends nagiosTask
{
    protected $enableDB     = true;
    protected $description  = 'Check that the database does not contain duplicate categories';
    
    /**
     * Core nagios method. Impliments method from parent class.
     * @param array $arguments
     * @param array $options
     */
    protected function executeNagiosTask( $arguments = array(), $options = array() )
    {
        $this->_findDuplicatePoiCategories();
        $this->_findUnusedPoiCategories();

        $this->_findDuplicateEventCategories();
        $this->_findUnusedEventCategories();
    }

    /**
     * Find Duplicate Poi Categories.
     */
    private function _findDuplicatePoiCategories()
    {
        $q = Doctrine::getTable( 'VendorPoiCategory' )
            ->createQuery()
            ->select('name as name, count(*) as c')
            ->groupBy('name, vendor_id')
            ->having('count(*) > 1')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalDuplicatePoiCategories = (int) count( $q );

        if( $totalDuplicatePoiCategories > 0 )
        {
            $this->addError( "Database contains {$totalDuplicatePoiCategories} duplicate poi categories." );
        }
    }
    
    /**
     * Find Unused Poi Categories.
     */
    private function _findUnusedPoiCategories()
    {
        $unusedPoiCategories = Doctrine::getTable( 'VendorPoiCategory' )->createQuery('vpc')
            ->where('vpc.id NOT IN ( SELECT lvpc.vendor_poi_category_id FROM LinkingVendorPoiCategory lvpc )')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalUnusedPoiCategories = (int) count( $unusedPoiCategories );

        if( $totalUnusedPoiCategories > 0 )
        {
            $this->addError( "Database contains {$totalUnusedPoiCategories} unused poi categories." );
        }
    }

    /**
     * Find Duplicate Event Categories.
     */
    private function _findDuplicateEventCategories()
    {
        $q = Doctrine::getTable( 'VendorEventCategory' )
            ->createQuery()
            ->select('name as name, count(*) as c')
            ->groupBy('name, vendor_id')
            ->having('count(*) > 1')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalDuplicateEventCategories = (int) count( $q );

        if( $totalDuplicateEventCategories > 0 )
        {
            $this->addError( "Database contains {$totalDuplicateEventCategories} duplicate event categories." );
        }
    }

    /**
     * Find Unused Event Categories.
     */
    private function _findUnusedEventCategories()
    {
        $unusedEventCategories = Doctrine::getTable( 'VendorEventCategory' )->createQuery('vec')
            ->where('vec.id NOT IN ( SELECT lvpc.vendor_event_category_id FROM LinkingVendorEventCategory lvpc )')
            ->execute( array(), Doctrine::HYDRATE_ARRAY );

        $totalUnusedEventCategories = (int) count( $unusedEventCategories );

        if( $totalUnusedEventCategories > 0 )
        {
            $this->addError( "Database contains {$totalUnusedEventCategories} unused event categories." );
        }
    }
}