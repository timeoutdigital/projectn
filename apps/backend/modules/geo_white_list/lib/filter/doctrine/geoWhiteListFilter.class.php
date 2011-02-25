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
    public function  configure() {
        parent::configure();

        $this->setWidget( 'showmarked', new sfWidgetFormSelect( array( 'choices' => array('hide' => 'Non-Whitelisted', 'show' => 'Whitelisted') )) );
        $this->setValidator( 'showmarked', new sfValidatorPass() );
        
    }
    public function  doBuildQuery(array $values)
    {
        // Apply default vendor when no vendor applied in filter
        $filters = sfContext::getInstance()->getUser()->getAttribute( 'geo_white_list.filters', array(), 'admin_module' );
        if( !isset($filters['vendor_id']) || $filters['vendor_id'] == null )
        {
            $filters['vendor_id'] = 1;
            $values['vendor_id'] = 1;
            sfContext::getInstance()->getUser()->setAttribute( 'geo_white_list.filters', $filters, 'admin_module' );
        }

        // Default, show only the unmarked once...
        if( !isset( $values['showmarked'] ) || null === $values['showmarked'] || '' === $values['showmarked'] )
        {
            $values['showmarked'] = 'hide';
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
        $query->andWhere( "{$alias}.latitude is not null" );
        $query->andWhere( "{$alias}.longitude is not null" );
        $query->andWhereIn( "CONCAT( {$alias}.latitude, ', ', {$alias}.longitude )" , $duplicates );
        
        return $query;
    }

    /**
     * Switch between shwoing marked and un-marked pois
     * @param Doctrine_Query $query
     * @param string $field
     * @param string $value
     * @return Doctrine_Query
     */
    public function addShowmarkedColumnQuery(Doctrine_Query $query, $field, $value)
    {
        if( $value === 'show' )
        {
            $query->andWhere( 'm.id IS NOT NULL' );
        } else {
            $query->andWhere( 'm.id IS NULL' );
        }

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
        $fields['showmarked'] = 'custom';
        return $fields;
    }
}