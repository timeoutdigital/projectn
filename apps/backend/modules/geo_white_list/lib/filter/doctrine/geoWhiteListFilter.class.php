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

class geoWhiteListFilter extends BasePoiFormFilter
{
    public function  doBuildQuery(array $values)
    {
        // Apply default vendor when no vendor applied in filter
        $filters = sfContext::getInstance()->getUser()->getAttribute( 'geo_white_list.filters', array(), 'admin_module' );
        if( !isset($filters['vendor_id']) || $filters['vendor_id'] == null )
        {
            $filters['vendor_id'] = 1;
            sfContext::getInstance()->getUser()->setAttribute( 'geo_white_list.filters', $filters, 'admin_module' );
        }
        
        $query = parent::doBuildQuery($values);
        // apply default filter to only return duplicates
        $duplicatesResults = Doctrine::getTable( 'Poi' )->findAllDuplicateLatLongs( $filters['vendor_id'] );
        $duplicates = array();
        foreach( $duplicatesResults as $record )
        {
            $duplicates[] = $record['myString']; // CONCAT( latitude, ", ", longitude ) as myString
        }

        $alias = $query->getRootAlias();
        $query->leftJoin( "{$alias}.PoiMeta m WITH m.lookup = 'geocodeWhitelist' " );
        $query->andWhere( 'm.id IS NULL' );
        $query->andWhere( "{$alias}.latitude is not null" );
        $query->andWhere( "{$alias}.longitude is not null" );
        $query->andWhereIn( "CONCAT( {$alias}.latitude, ', ', {$alias}.longitude )" , $duplicates );
        
        return $query;
    }

    /**
     * This method can be used to override the default fields
     * and add dynamic custom fields for filtering
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        return $fields;
    }
}