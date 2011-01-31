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
    public function  buildQuery(array $values) 
    {

        // Due to large number of Pois that we have, it's necessary to limit per vendor base.
        // This line Force NY as default vendor.
        $filters = sfContext::getInstance()->getUser()->getAttribute( 'duplicate_pois.filters', array(), 'admin_module' );
        if( !isset($filters['vendor_id']) || $filters['vendor_id'] == null )
        {
            $filters['vendor_id'] = 1;
            sfContext::getInstance()->getUser()->setAttribute( 'duplicate_pois.filters', $filters, 'admin_module' );
        }
        
        $query = parent::buildQuery($values);
        $this->addLogicTo( $query );
        return $query;
    }

    private function addLogicTo( Doctrine_Query &$query )
    {
        $poi = $query->getRootAlias();

        // exclude the Duplicates in the PoiReference
//        $query->leftJoin("{$poi}.PoiReference pr ON pr.duplicate_poi_id = {$poi}.id");
//        $query->andWhere( "pr.master_poi_id IS NULL" );

        // Filter select to Minimum required
//        $query->select( "{$poi}.*");
        //$query->select( "{$poi}.id, {$poi}.poi_name, {$poi}.latitude, {$poi}.longitude, COUNT(*) as dupes");
        
        // Exclude those lat/long in GeoWhitelist
        //$query->andWhere( " CONCAT( {$poi}.latitude, ',', {$poi}.longitude ) IN ( SELECT CONCAT( latitude, ',', longitude ) FROM GeoWhiteList )" );
        $query->andWhere( "CONCAT( {$poi}.latitude, ',', {$poi}.longitude ) NOT IN ( SELECT CONCAT( w.latitude, ',', w.longitude ) FROM GeoWhiteList w)");
        $query->andWhere( "{$poi}.id NOT IN ( SELECT pr.duplicate_poi_id FROM PoiReference pr)" );

        $query->addSelect( 'COUNT(*) as dupes, id, poi_name, latitude, longitude' );
        // Not null lat / long
        $query->andWhere( "{$poi}.latitude IS NOT NULL" );
        $query->andWhere( "{$poi}.longitude IS NOT NULL" );

        // Group by Poi_name, Latitude, longitude
        $query->groupBy( " {$poi}.poi_name, {$poi}.latitude, {$poi}.longitude" );
        $query->having( 'COUNT(*) > 1 ' );
    }
}