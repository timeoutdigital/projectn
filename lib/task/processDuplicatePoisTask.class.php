<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class processDuplicatePois extends sfBaseTask
{
    protected function configure()
    {

    $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','backend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
        new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'One city at a time, please provice a valid City name [--city=london]'),
  
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'processDuplicatePois';
        $this->briefDescription = 'Auto Select and Map master/Duplicate pois';
        $this->detailedDescription = "Use logic to select the Best Master out of foudn duplicate pois and map them in PoiReference Table";
    }

    protected function execute($arguments = array(), $options = array())
    {
        // Estabilish Database Connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        $this->getDuplicateByGeocodeAndName( 4 );
    }
    
    private function getDuplicateByGeocodeAndName( $vendorID )
    {
        $q = Doctrine::getTable( 'Poi' )->createQuery( 'p' )
                ->leftJoin( 'p.PoiReference r ON p.id = r.duplicate_poi_id' )
                ->select( 'p.poi_name, p.latitude, p.longitude, count(*) as dupes' )
                ->where( 'r.master_poi_id IS NULL ' )
                ->andWhere( 'p.vendor_id = ?', $vendorID )
                ->groupBy( 'p.latitude, p.longitude, p.poi_name' )
                ->having( 'dupes > 1 ');
        var_dump( $q->getSqlQuery() );
    }

    /**
     * Pick UI Category with highest business value.
     * @return string of highest UI Category or false on failure.
     */
    protected function pickHighestValueCategory( array $cats )
    {
        if( empty( $cats ) ) return false;

        $priority = array( 'Eating & Drinking', 'Film', 'Art', 'Around Town', 'Nightlife', 'Music', 'Stage' );
        $highestCategory = 99999;

        foreach( $cats as $cat )
        {
            $priorityValue = array_search( $cat, $priority );
            
            if( is_numeric( $priorityValue ) && $priorityValue < $highestCategory )
                $highestCategory = $priorityValue;

            if( $highestCategory === 0 ) break;
        }

        return ( array_key_exists( $highestCategory, $priority ) ) ? $priority[ $priorityValue ] : false;
    }
}