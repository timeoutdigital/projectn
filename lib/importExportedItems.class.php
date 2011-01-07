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

    public function import()
    {
        $modelDirList = DirectoryIteratorN::iterate( $this->modelsFolderPath, DirectoryIteratorN::DIR_FOLDERS );
        if( !is_array( $modelDirList ) || count( $modelDirList ) != 3 )
        {
            throw new ImportExportedItemsException( 'Invalid number of Model dir found! should be 3 (poi, event and movie). found :'. is_array($modelDirList) ? count($modelDirList) : 'ERROR' );
        }

        foreach( $modelDirList as $modelDir )
        {
            $modelDirFullPath = $this->modelsFolderPath . '/' . $modelDir;

            // Get all the XML fils in this Model
            $modelExportedCities = DirectoryIteratorN::iterate( $modelDirFullPath, DirectoryIteratorN::DIR_FILES, 'xml' );
            if( !is_array( $modelExportedCities ) || empty($modelExportedCities) )
            {
                throw new ImportExportedItemsException( "Exported City file not found for Model {$modelDir} in {$modelDirFullPath}" );
            }
            
            // Import each of these City files into Exporteditems
            foreach( $modelExportedCities as $cityFileName )
            {
                $cityFileFullPath = $modelDirFullPath . '/' . $cityFileName;

                $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $this->fileName2City( $cityFileName ) );
                if( $vendor === false )
                {
                    throw new ImportExportedItemsException( 'City vendor not found for city name: ' . $this->fileName2City( $cityFileName ) );
                }

                // Import Exported Data
                $this->importExportedXml( simplexml_load_file( $cityFileFullPath ), $modelDir, $vendor );
            }
        }
    }

    private function importExportedXml( $xmlData, $model, Vendor $vendor )
    {
        $xmlNodes = array();
        
        foreach( $xmlData as $node )
        {
            isset( $xmlNodes["{$model} {$vendor['city']}"] ) ? $xmlNodes["{$model} {$vendor['city']}"]++ : $xmlNodes["{$model} {$vendor['city']}"] = 0;
            //var_dump( "{$model} {$vendor['city']}" );
            //Doctrine::getTable( 'ExportedItem' )->saveRecord( $node, $model, $vendor['id'] );
        }
        print_r( $xmlNodes );
    }

    private function fileName2City( $fileName )
    {
        if( !is_string($fileName) || trim($fileName) == '' )
        {
            return null;
        }

        // remove XML and remove _
        $fileName = explode( '.', $fileName, 2 );
        return trim( str_replace('_',' ', $fileName[0] ) );
    }
}

class ImportExportedItemsException extends Exception{}