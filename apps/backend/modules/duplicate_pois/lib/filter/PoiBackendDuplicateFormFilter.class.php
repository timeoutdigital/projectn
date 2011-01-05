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

class PoiBackendDuplicateFormFilter extends BasePoiFormFilter
{
    public function configure()
    {
        parent::configure();
    }

    /* This will help us with app */
    public function  buildQuery(array $values) {

        // makesure that Vendor ID is allways applied
        // because group sql query will consume huge amount of memory and processing power

        if( $values['vendor_id'] == null ) // == bcz 0 should not be there too
        {
            $values['vendor_id'] = 1;
        }

        $query = parent::buildQuery($values);
        $this->addLogicTo( $query );

        var_dump( $query->getSqlQuery() );
        return $query;
    }

    private function addLogicTo( Doctrine_Query &$query )
    {
        $poi = $query->getRootAlias();
        
        // Exclude those lat/long in GeoWhitelist
        //$query->andWhere( "CONCAT( {$poi}.latitude, ',', {$poi}.longitude ) NOT IN ( SELECT CONCAT( g2.latitude, ',', g2.longitude ) FROM GeoWhiteList g2)" );

        // Not null lat / long
        $query->andWhere( "{$poi}.latitude IS NOT NULL" );
        $query->andWhere( "{$poi}.longitude IS NOT NULL" );

        // exclude the Duplicates in the PoiReference
        $query->leftJoin("{$poi}.PoiReference pr ON pr.duplicate_poi_id = {$poi}.id");
        $query->andWhere( "pr.master_poi_id IS NULL" );

        // Filter select to Minimum required
//        $query->select( "{$poi}.id, {$poi}.poi_name, {$poi}.latitude, {$poi}.longitude");
//        $query->addSelect( 'COUNT(*) as dupes ');

        // Group by Poi_name, Latitude, longitude
        $query->groupBy( "{$poi}.poi_name, {$poi}.latitude, {$poi}.longitude" );
        $query->having( 'COUNT(*) > 1 ' );
    }
}