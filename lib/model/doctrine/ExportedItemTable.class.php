<?php

class ExportedItemTable extends Doctrine_Table
{
    
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
            // Find existing or create new
            $record = $this->findOneByModelAndRecordId( $modelType, $recordID );

            if( $record === false ) // add this as new Record
            {
                $record = new ExportedItem;
                $record['record_id'] = $recordID;
                $record['model'] = $modelType;
                $record['ui_category_id'] = $ui_category_id;
                $record['vendor_id'] = $vendorID;
            }
            else // Update any Modification changes
            {
                if( $record['ui_category_id'] != $ui_category_id )
                {
                    $exportedItemModification = new ExportedItemModification;
                    $exportedItemModification['field'] = 'ui_category_id';
                    $exportedItemModification['value_before_change'] = $record['ui_category_id'];

                    $record['ExportedItemModification'][] = $exportedItemModification;
                    $record['ui_category_id'] = $ui_category_id;
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
        $propertyUICategory = array_unique( $xmlNode->xpath( '//property[@key = "UI_CATEGORY"]' ) );

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
        $uiCategory = Doctrine::getTable( 'UiCategory' )->findOneByName( $categoryName );
        
        return ( $uiCategory === false ) ? null : $uiCategory['id'];
    }
    
}

class ExportedItemTableException extends Exception {}