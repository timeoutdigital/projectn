<?php

class ExportedItemTable extends Doctrine_Table
{
    private static $uiCategoryCache;

    /**
     * Add or Update record to ExportedItemTable
     * @param SimpleXMLElement $xmlNode
     * @param string $modelType
     * @param int $vendorID
     */
    public function saveRecord( &$xmlNode, $modelType, $vendorID )
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
                     date('Y-m-d H:i:s', $modifiedDate )
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
                     date('Y-m-d H:i:s', $modifiedDate )
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
                    date('Y-m-d H:i:s', $modifiedDate )
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
        //$uiCategory = ( $categoryName !== null ) ?  Doctrine::getTable( 'UiCategory' )->findOneByName( $categoryName, Doctrine::HYDRATE_ARRAY ) : false;
        
        return $this->getUICategoryIdByName( $categoryName );//( !is_array($uiCategory) || empty($uiCategory) ) ? null : $uiCategory['id'];
    }

    private function getUICategoryIdByName( $categoryName )
    {
        // Fill Static Cache when null
        if( self::$uiCategoryCache === null )
        {
            self::$uiCategoryCache = Doctrine::getTable( 'UiCategory' )->findAll( Doctrine_Core::HYDRATE_ARRAY );
        }

        foreach( self::$uiCategoryCache as $cat )
        {
            if( $cat['name'] == $categoryName )
            {
                return $cat['id'];
            }
        }
        
        return null;

    }

    /**
     * Fetch Exported Item and Exported Item History by Date range, vendor, Model[poi,event,movie] and [ ui_category_id & invoiceableOnly ]
     * @param string $startDate
     * @param string $endDate
     * @param int $vendorID
     * @param string $modelType
     * @param array $invoiceableCategory
     * @param int $ui_category_id
     * @param boolean $invoiceableOnly
     * @return mixed
     */
    public function fetchBy( $startDate, $endDate, $vendorID, $modelType, $invoiceableCategory, $ui_category_id = null, $invoiceableOnly = true, $hhydrateMode = Doctrine_Core::HYDRATE_RECORD )
    {
        $startDateTime = strtotime( $startDate );
        $endDateTime = strtotime( $endDate );

        $q = $this->createQuery( 'e' )
                ->innerJoin( 'e.ExportedItemHistory h')
                ->where( 'e.vendor_id=?', $vendorID )
                ->andWhere( 'h.field= ?', "ui_category_id" )
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
            // Select the Date range from History
            $q->andWhere( 'DATE(h.created_at) >= ? AND DATE(h.created_at) <= ?', array( date('Y-m-d', $startDateTime ), date('Y-m-d', $endDateTime ) )  );

            // Check given Category is Invoiceable
            if( $ui_category_id && is_numeric( $ui_category_id ) && $ui_category_id > 0 &&
                !in_array( $ui_category_id, $invoiceableCategory) )
            {
                return null;
            }

            $q->andWhereIn( 'h.value' , $invoiceableCategory );
            $whereValueArray = array( $modelType, "ui_category_id", date( 'Y-m-d', $startDateTime )  );
            $inValues = implode('","', $invoiceableCategory );
            $q->andWhere( 'e.id NOT IN ( SELECT ee.id FROM ExportedItem ee INNER JOIN ee.ExportedItemHistory hh WHERE ee.model = ? AND hh.field= ? AND DATE(hh.created_at) < ? AND hh.value IN ( "'.$inValues.'" ) )', $whereValueArray );
        } else {
            $q->andWhere( 'DATE(e.created_at) >= ? AND DATE(e.created_at) <= ?', array( date('Y-m-d', $startDateTime ), date('Y-m-d', $endDateTime ) )  );
            $q->orderBy( 'h.created_at DESC');
        }
        
        return $q->execute( array(), $hhydrateMode );
    }
    
}

class ExportedItemTableException extends Exception {}