<?php

class ExportedItemTable extends Doctrine_Table
{
    // Cache UI category and related vendor poi/event category
    private static $uiCategoryCache;
    private static $poiUiCategoryMap;
    private static $eventUiCategoryMap;

    /**
     * Add or Update record to ExportedItemTable
     * @param SimpleXMLElement $xmlNode
     * @param string $modelType
     * @param int $vendorID
     */
    public function saveRecord( $xmlNode, $modelType, $vendorID, $modifiedTimeStamp )
    {
        // Pre-process
        $modelType = strtolower( $modelType );
        if( !in_array( $modelType, array( 'poi', 'event', 'movie' ) ) )
        {
            throw new ExportedItemTableException( "Invalid modelType, Should only be poi/event/movie" );
        }

        if( $modifiedTimeStamp == null || trim($modifiedTimeStamp) == '' )
        {
            throw new ExportedItemTableException( 'Invalid $modifiedTimeStamp in the parameter' );
        }
        
        // Get ID from xmlNode, Poi have attribue "vpid" for id and Event & Move had attribue "id" for their unique ID
        $recordID = ( $modelType == 'poi' ) ? (string)$xmlNode['vpid'] : (string)$xmlNode['id'];
        $recordID = intval( substr( $recordID , 3 ) ); // strip Airport code and 0's at front

        // Get UI category ID, No UI category = 0 ID
        $ui_category_id = ( $modelType == 'movie' ) ? 1 : $this->getUiCategoryIdUsingVendorCategory( $xmlNode, $modelType );
        if( $ui_category_id == null )
        {
            $ui_category_id = 0; // When no UI category found,set id as 0
        }

        try
        {
            // Get PDO object from Doctrine connection
            $pdoConn = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();

            // Find the Existing Record
            $sql = 'SELECT e.id, e.record_id, h.value, h.field FROM exported_item e INNER JOIN exported_item_history h on h.exported_item_id = e.id ';
            $sql .= 'WHERE e.model = ? AND e.record_id = ? AND h.field = "ui_category_id" ';
            $sql .= 'ORDER BY h.created_at DESC LIMIT 1';

            $query = $pdoConn->prepare( $sql );
            $status = $query->execute( array(
                $modelType,
                $recordID
            ));
            $record = ( $status === true ) ? $query->fetch() : null;
            
             if( !is_array( $record ) || empty( $record ) ) // add this as new Record
             {

                 $query = $pdoConn->prepare( 'INSERT into `exported_item`(record_id, model, vendor_id, created_at) VALUES(?,?,?,?)' );
                 $status = $query->execute( array(
                     $recordID,
                     $modelType,
                     $vendorID,
                     date('Y-m-d H:i:s', $modifiedTimeStamp )
                 ));
                  
                 if( $status !== true )
                 {
                     throw new ExportedItemTableException( "Failed to Insert New Exported Item Record." );
                 }

                 $query = $pdoConn->prepare( 'INSERT INTO `exported_item_history` ( exported_item_id, field, value, created_at) VALUES( ?, ?, ?, ?)' );
                 $status = $query->execute( array(
                     $pdoConn->lastInsertId(), 
                     'ui_category_id',
                     $ui_category_id,
                     date('Y-m-d H:i:s', $modifiedTimeStamp )
                 ));

                 if( $status !== true )
                 {
                     throw new ExportedItemTableException( "Failed to Insert New Exported Item History Record." );
                 }

             } elseif( $record['value'] != $ui_category_id ) { // Update any Modification changes

                $query = $pdoConn->prepare( 'INSERT INTO `exported_item_history` ( exported_item_id, field, value, created_at) VALUES( ?, ?, ?, ?)' );
                $status = $query->execute( array(
                    $record['id'],
                    'ui_category_id',
                    $ui_category_id,
                    date('Y-m-d H:i:s', $modifiedTimeStamp )
                ));

                if( $status !== true )
                {
                 throw new ExportedItemTableException( "Failed to Insert New Exported Item History Record." );
                }

             }

        } catch ( Exception $e ) {
            // @todo: process Exception
            throw $e;
        }
    }


    /**
     * Pick UI Category with highest business value.
     * @param array $uiCateegoriesArray
     * @return mixed
     */
    private function getHighestValueUICategoryID( $uiCategoriesArray )
    {
        
        if( !is_array( $uiCategoriesArray ) ||  empty( $uiCategoriesArray ) )
        {
            return null;
        }

        // For each Unique UI category, Find the Best (Money value) category for this Record
        $highestCategory = 99999;

        // get category priority from config/app.yaml
        $priority = sfConfig::get( 'app_ui_category_priority' );
        
        // Loopthrough each UI category to find the BEST one
        foreach( $uiCategoriesArray as $category )
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

        // Get ARRAY_INDEX's Value (Ui category Name)
        $categoryName = ( array_key_exists( $highestCategory, $priority ) ) ? $priority[ $highestCategory ] : null;

        if($categoryName == null ) return null;

        // look up for the UI Category ID and retuern
        foreach( self::$uiCategoryCache as $cat )
        {
            if( $cat['name'] == $categoryName )
            {
                return $cat['id'];
            }
        }

        return null; // nothing found? return null
    }

    /**
     * Tidy up category names and return clean list of vendor categories
     * @param array $categoryList
     * @return mixed
     */
    private function getUiCategoryIdUsingVendorCategory( SimpleXMLElement &$xmlNode, $modelType )
    {
        // Load UI categories when cache is null
        if( self::$uiCategoryCache === null ) $this->loadUICategoryAndVendorCategory();

        // Extract the vendor categories from XML node
        $vendorCategories = $xmlNode->xpath( './/vendor-category' );

        // Use linking category based on Model.
        $linkingCategory = ( $modelType == 'poi' ) ? self::$poiUiCategoryMap : self::$eventUiCategoryMap;

        // loopthrough each vendor categories and match related UI categories,
        // then use getHighestValueUICategoryID() to get the Best UI category
        $uiCategoryArray = array();
        foreach( $vendorCategories as $cat )
        {
            // Clean the category name for Whitespaces
            $catName = str_replace( PHP_EOL, ' ', html_entity_decode( stringTransform::mb_trim( (string) $cat ) ) );
            
             // Check that we have the category in the mapping array.
            if( in_array( $catName, array_keys( $linkingCategory ) ) )
            {
                // Add found UI category to list, This than will be filtered for Unique and returned as array of String ui category names
                $uiCategoryArray[] =  $linkingCategory[ $catName ];
            }

        }

        return $this->getHighestValueUICategoryID( array_unique( $uiCategoryArray ) );
    }

    /**
     * Get the best value UI category ID using UI Category in the Feed
     * @param SimpleXMLElement $xmlNode
     * @return mixed
     */
    private function getUiCategoryIdInFeed( SimpleXMLElement &$xmlNode )
    {
        // Load UI categories when cache is null
        if( self::$uiCategoryCache === null ) $this->loadUICategoryAndVendorCategory();
        
        $uiCategories = array_unique( $xmlNode->xpath( './/property[@key="UI_CATEGORY"]' ) );

        $uiCategoryNames = array();
        foreach( $uiCategories as $cat )
        {
            $uiCategoryNames[] = (string)$cat;
        }

        return $this->getHighestValueUICategoryID( $uiCategoryNames );
    }

    /**
     * Load UI category and related vendor categories into static cache
     */
    private function loadUICategoryAndVendorCategory()
    {
        self::$uiCategoryCache = array();
        self::$poiUiCategoryMap = array();
        self::$eventUiCategoryMap = array();

        // Get mapping data from external database (usually prod).
        foreach( Doctrine::getTable('UiCategory')->findAll() as $map )
        {
            self::$uiCategoryCache[] = array( 'name' => $map['name'] , 'id' => $map['id'] );
            foreach( $map['VendorPoiCategory'] as $m )   self::$poiUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
            foreach( $map['VendorEventCategory'] as $m ) self::$eventUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
        }
        
        if( empty( self::$uiCategoryCache ) )
        {
            throw new ExportedItemException( 'Could not get category mappings from database, please specify a live data source.' );
        }
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
            $q->andWhere( 'e.id NOT IN ( SELECT ee.id 
                                         FROM ExportedItem ee
                                         INNER JOIN ee.ExportedItemHistory hh
                                         WHERE ee.model = ?
                                         AND hh.field= ?
                                         AND DATE(hh.created_at) < ?
                                         AND hh.value IN ( "'.$inValues.'" ) )', $whereValueArray );
        }
        
        return $q->execute();
    }
    
}

class ExportedItemTableException extends Exception {}