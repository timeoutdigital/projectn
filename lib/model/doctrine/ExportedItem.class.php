<?php

/**
 * ExportedItem
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class ExportedItem extends BaseExportedItem
{
    public function isInvoiceable( $startDate, $endDate )
    {
        // Convert all Date time String to time() unix format
        $updated_at =  strtotime( substr($this['updated_at'],0,10 ) );
        $startDate = strtotime( $startDate );
        $endDate = strtotime( $endDate );

        // Read UICategory YAMl to identify the Invoiceable Category
        $invoiceableCategories = sfYaml::load( file_get_contents( sfConfig::get( 'sf_config_dir' ) . '/invoiceableCategory.yml' ) );
        $invoiceableCategoryIDs = array_keys( $invoiceableCategories['invoiceable']);

        // Check current UI category for this Item is Invoiceable only when updated fall within daterange
        if( ( $updated_at  >= $startDate &&  $updated_at <=  $endDate ) &&
            ( !in_array( $this['ui_category_id'], $invoiceableCategoryIDs ) ) )
        {   
            return false;  
        }

        // at this point, This record IS Invoiceable.
        // When updated date is not within daterange, we will have to query Modification on or before the start_date to ensure that
        // this record was not invoiceable before start_date
        $q = Doctrine::getTable('ExportedItemModification')->createQuery( 'm' )
                ->where( 'exported_item_id = ? ', $this['id'] )
                ->andWhere( 'created_at <= ?', date('Y-m-d', $startDate ) )
                ->andWhere( 'field = ?', 'ui_category_id' )
                ->andWhereIn( 'value_before_change',  $invoiceableCategoryIDs );
        $results = $q->execute();

        // Query did find a category that was invoiceable?
        if( $results->count() > 0 )
        {
            return false;
        }

        // true = ui_category modified between $startDate and $endDate && ui_category is now chargeable but was not before $startDate
        return true;
    }
}
