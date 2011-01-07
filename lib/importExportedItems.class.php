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

class importExportedItems
{
    /**
     * Folder path to read the Models [Poi, Movie, Event]
     * Typicaly this would be "exports/export_yyyymmdd" path
     * @var string
     */
    private $modelsFolderPath;

    /**
     * Folder path to Date based export folder, where Poi, event and Movie folders exists
     * @param string $modelsFolderPath
     */
    public function  __construct( $modelsFolderPath )
    {
        if( !is_dir( $modelsFolderPath ) )
        {
            throw new ImportExportedItemsException( "ModelsFolderPath given in constructor parameter not found" );
        }
        
        $this->modelsFolderPath = $modelsFolderPath;
    }
}

class ImportExportedItemsException extends Exception{}