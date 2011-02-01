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
        $modifiedDate = strtotime( (string)$xmlNode['modified'] );

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
                 $record['created_at'] = date('Y-m-d H:i:s', $modifiedDate );

                 // add History Record
                 $recordHistory = new ExportedItemHistory;
                 $recordHistory['field'] = 'ui_category_id';
                 $recordHistory['value'] = $ui_category_id;
                 $recordHistory['created_at'] = date('Y-m-d H:i:s', $modifiedDate );
                 $record['ExportedItemHistory'][] = $recordHistory;

             } else { // Update any Modification changes

                 if( $record['ExportedItemHistory'][0]->value != $ui_category_id)
                 {
                     $recordHistory = new ExportedItemHistory;
                     $recordHistory['field'] = 'ui_category_id';
                     $recordHistory['value'] = $ui_category_id;
                     $recordHistory['created_at'] = date('Y-m-d H:i:s', $modifiedDate );
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

        // get category priority from config/app.yaml
        $priority = sfConfig::get( 'app_ui_category_priority' );

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

        $categoryName = ( array_key_exists( $highestCategory, $priority ) ) ? $priority[ $highestCategory ] : null;
        $uiCategory = ( $categoryName !== null ) ?  Doctrine::getTable( 'UiCategory' )->findOneByName( $categoryName ) : false;
        
        return ( $uiCategory === false ) ? null : $uiCategory['id'];
    }

    /**
     * Fetch Exported Item and Exported Item History by Date range, vendor, Model[poi,event,movie] and [ ui_category_id & invoiceableOnly ]
     * @param string $startDate
     * @param string $endDate
     * @param int $vendorID
     * @param string $modelType
     * @param int $ui_category_id
     * @param boolean $invoiceableOnly
     * @return mixed
     */
    public function fetchBy( $startDate, $endDate, $vendorID, $modelType, $ui_category_id = null, $invoiceableOnly = true )
    {
        $startDateTime = strtotime( $startDate );
        $endDateTime = strtotime( $endDate );


        $q = $this->createQuery( 'e' )
                ->innerJoin( 'e.ExportedItemHistory h')
                ->where( 'e.vendor_id=?', $vendorID )
                ->andWhere( 'h.field= ?', "ui_category_id" )
                ->andWhere( 'DATE(h.created_at) BETWEEN ? AND ? ', array( date('Y-m-d', $startDateTime ), date('Y-m-d', $endDateTime ) )  )
                ->andWhere( 'e.model = ? ', $modelType );

        // Makesure to select the Last/Latest Category ID
        if( isset( $ui_category_id ) && is_numeric( $ui_category_id ) && $ui_category_id > 0 )
        {
            $q->andWhere( 'h.value = ?', $ui_category_id );
            $q->groupBy( 'e.id' );
            $q->orderBy( 'h.created_at DESC');
            //$q->andWhere( 'h.id = ( SELECT MAX(eh.id) FROM ExportedItemHistory eh WHERE eh.field= ? AND ( DATE(eh.created_at) BETWEEN ? AND ?) AND eh.exported_item_id = e.id)', array( "ui_category_id", date('Y-m-d', $startDateTime), date('Y-m-d', $endDateTime)) );
        }
        
        if( $invoiceableOnly )
        {
            $invoiceableYaml = sfYaml::load( file_get_contents( sfConfig::get( 'sf_config_dir' ) . '/invoiceableCategory.yml' ) );
            $invoiceableCategoryIDs = array_keys( $invoiceableYaml['invoiceable'] );

            // Check given Category is Invoiceable
            if( $ui_category_id && is_numeric( $ui_category_id ) && $ui_category_id > 0 &&
                !in_array( $ui_category_id, $invoiceableCategoryIDs) )
            {
                return null;
            }

            $q->andWhereIn( 'h.value' , $invoiceableCategoryIDs );
            $whereValueArray = array( $modelType, "ui_category_id", date( 'Y-m-d', $startDateTime )  );
            $inValues = implode('","', $invoiceableCategoryIDs );
            $q->andWhere( 'e.id NOT IN ( SELECT ee.id FROM ExportedItem ee INNER JOIN ee.ExportedItemHistory hh WHERE ee.model = ? AND hh.field= ? AND DATE(hh.created_at) < ? AND hh.value IN ( "'.$inValues.'" ) )', $whereValueArray );
        }
        
        return $q->execute();
    }
    
}

class ExportedItemTableException extends Exception {}