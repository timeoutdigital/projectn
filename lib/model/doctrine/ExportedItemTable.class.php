<?php

class ExportedItemTable extends Doctrine_Table
{

    /**
     * Add or Update record to ExportedItemTable
     * @param SimpleXMLElement $xmlNode
     * @param string $modelType
     * @param int $vendorID
     */
    public function saveRecord( $xmlNode, $modelType, $vendorID )
    {
        // Pre-process
        $modelType = strtolower( $modelType );
        if( !in_array( $modelType, array( 'poi', 'event', 'movie' ) ) )
        {
            throw new ExportedItemTableException( "Invalid modelType, Should only be poi/event/movie" );
        }
        
        // Get ID from xmlNode, Poi have attribue "vpid" for id and Event & Move had attribue "id" for their unique ID
        $recordID = ( $modelType == 'poi' ) ? (string)$xmlNode['vpid'] : (string)$xmlNode['id'];
        $recordID = intval( substr( $recordID , 3 ) ); // strip Airport code and 0's at front

        // Get UI category ID, No UI category = 0 ID
        $ui_category_id = $this->getHighestValueUICategoryID( $xmlNode );
        if( $ui_category_id == null )
        {
            $ui_category_id = 0; // When no UI category found,set id as 0
        }

        try
        {
            // Find the Existing Record
            $query = $this->createQuery( 'e' )
                    ->leftJoin( 'e.ExportedItemHistory h' )
                    ->select( 'e.id, e.record_id, h.field, h.value' )
                    ->where( 'e.model = ? ', $modelType )
                    ->andWhere( 'e.record_id = ? ', $recordID )
                    ->andWhere( 'h.field = ? ', "ui_category_id" )
                    ->orderBy( 'h.created_at DESC' );
                    // adding Limit cause SubQuery?
             $record = $query->fetchOne();//fetchOne( array(), Doctrine_Core::HYDRATE_ARRAY );

             if( $record === false ) // add this as new Record
             {
                 $record = new ExportedItem;
                 $record['record_id'] = $recordID;
                 $record['model'] = $modelType;
                 $record['vendor_id'] = $vendorID;

                 // add History Record
                 $recordHistory = new ExportedItemHistory;
                 $recordHistory['field'] = 'ui_category_id';
                 $recordHistory['value'] = $ui_category_id;
                 $record['ExportedItemHistory'][] = $recordHistory;

             } else { // Update any Modification changes

                 if( $record['ExportedItemHistory'][0]->value != $ui_category_id)
                 {
                     $recordHistory = new ExportedItemHistory;
                     $recordHistory['field'] = 'ui_category_id';
                     $recordHistory['value'] = $ui_category_id;
                     $record['ExportedItemHistory'][] = $recordHistory;
                 }
             }

            $record->save();

        } catch ( Exception $e ) {
            // @todo: process Exception
            throw $e;
        }
    }

    /**
     * Pick UI Category with highest business value.
     * @param SimpleXMLElement $xmlNode
     * @return string of highest UI Category or false on failure.
     */
    private function getHighestValueUICategoryID( $xmlNode )
    {
        // Extract UI category from xmlNode and Get only the Unique Category
        $propertyUICategory = array_unique( $xmlNode->xpath( './/property[@key="UI_CATEGORY"]' ) );

        if( empty( $propertyUICategory ) )
        {
            return null;
        }

        // For each Unique UI category, Find the Best (Money value) category for this Record
        $highestCategory = 99999;
        $priority = array( 'Eating & Drinking', 'Film', 'Art', 'Around Town', 'Nightlife', 'Music', 'Stage' );

        foreach( $propertyUICategory as $category )
        {
            $uiCatName = (string)$category;

            $priorityValue = array_search( $uiCatName, $priority );
            if( is_numeric( $priorityValue ) && $priorityValue < $highestCategory )
            {
                $highestCategory = $priorityValue;
            }

            if( $highestCategory === 0 )
            {
                break;
            }
        }

        $categoryName = ( array_key_exists( $highestCategory, $priority ) ) ? $priority[ $priorityValue ] : null;
        $uiCategory = ( $categoryName !== null ) ?  Doctrine::getTable( 'UiCategory' )->findOneByName( $categoryName ) : false;
        
        return ( $uiCategory === false ) ? null : $uiCategory['id'];
    }

    public function fetchBy( $startDate, $endDate, $vendorID, $ui_category_id, $invoiceableOnly = true )
    {
        
    }
    
}

class ExportedItemTableException extends Exception {}