<?php

class ExportedItemTable extends Doctrine_Table
{
    public function saveRecord( $xmlNode, $modelType )
    {
        // Pre-process
        $modelType = strtolower( $modelType );
        if( !in_array( $modelType, array( 'poi', 'event', 'movie' ) ) )
        {
            throw new ExportedItemTableException( "Invalid modelType, Should only be poi/event/movie" );
        }
        
        // Get ID from xmlNode, Poi have attribue "vpid" for id and Event & Move had attribue "id" for their unique ID
        $recordID = ( $modelType == 'poi' ) ? $xmlNode['vpid'] : $xmlNode['id'];

        $record = $this->findOneByModelAndRecordId( $modelType, $recordID );

        if( $record === false ) // add this as new Record
        {
            $record = new ExportedItemTable;
            $record['record_id'] = $recordID;
            $record['model'] = $modelType;
            
        }
        else // Update any Modification changes
        {
            
        }
    }

    private function _getUICategoryID( $xmlNode )
    {
        $propertyUICategory = $xmlNode->xpath( '//property[key="UI_CATEGORY"]/text()' );
    }
}

class ExportedItemTableException extends Exception {}