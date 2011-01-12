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
     * Get Items First exported within given date range per vendor and Model
     * @param string $startDate
     * @param string $endDate
     * @param int $vendorID
     * @param string $model
     * @return mixed
     */
    public function getItemsFirstExportedIn( $startDate, $endDate, $vendorID, $model )
    {
        // Best to have time in Unix format
        $startDateStamp= strtotime( $startDate );
        $endDateStamp = strtotime( $endDate );

        // Get PDO from Doctrine for Direct DB query (Doctrine Takes Longer time and Memory)
        $pdoDB = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();

        $sql = 'SELECT e.*, h.field, h.value FROM exported_item e LEFT JOIN exported_item_history h ON e.id = h.exported_item_id ';
        $sql .= 'WHERE ';
        $sql .= 'e.vendor_id = ? ';
        $sql .= 'AND e.model = ? ';
        $sql .= 'AND h.field = ? ';
        $sql .= 'AND ( DATE(e.created_at) >= ? AND DATE(e.created_at) <= ? ) ';
        $sql .= ' ORDER BY h.created_at DESC ';

        $query = $pdoDB->prepare( $sql );
        $status = $query->execute( array(
            $vendorID,
            $model,
            'ui_category_id',
            date('Y-m-d', $startDateStamp ),
            date('Y-m-d', $endDateStamp )
        ));

        return ($status) ? $query->fetchAll() : null;
    }
    
}

class ExportedItemTableException extends Exception {}