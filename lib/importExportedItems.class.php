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

    private $exceptionCityName = array( 'beijing_zh', 'istanbul_en', 'shanghai_zh', 'shanghai_en' );

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
            echo ' - '.date('d/m/Y H:i:s').': Importing Model: ' . $modelDir . PHP_EOL;
            
            $modelDirFullPath = $this->modelsFolderPath . '/' . $modelDir;

            // Get all the XML fils in this Model
            $modelExportedCities = DirectoryIteratorN::iterate( $modelDirFullPath, DirectoryIteratorN::DIR_FILES, 'xml' );
            if( !is_array( $modelExportedCities ) || empty($modelExportedCities) )
            {
                echo "ERROR: Exported City file not found for Model {$modelDir} in {$modelDirFullPath}" . PHP_EOL;
                continue;
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
                echo "\tCITY:\t" . $vendor['city'] . PHP_EOL;
                $xmlDATA = simplexml_load_file( $cityFileFullPath );
                $this->importExportedXml( $xmlDATA, $modelDir, $vendor );
                unset( $xmlDATA );
                unset( $vendor );
            }
        }
    }

    private function importExportedXml( &$xmlData, $model, Vendor $vendor )
    {
        // Extract Modified time from ROOT element
        $modifiedString = (string)$xmlData['modified'];
        if( trim( $modifiedString ) == '' )
        {
            echo 'ERROR: No modified date fround on the Document' . PHP_EOL;
            return;
        }
        
        // Format to Unix time stamp
        $modifiedStamp = strtotime( $modifiedString );
        foreach( $xmlData as $node )
        {
            // Call saveRecord, logic for saving / udpating is handled inside saveRecord() #860
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $node, $model, $vendor['id'], $modifiedStamp );
        }
    }

    private function fileName2City( $fileName )
    {
        if( !is_string($fileName) || trim($fileName) == '' )
        {
            return null;
        }

        // remove XML and remove _
        $fileName = explode( '.', $fileName, 2 );
        $cityName = trim($fileName[0]);

        // Make exception for Delhi
        $cityName = ( $cityName == 'dehli') ? 'delhi' : $cityName;
        
        // Some city name should be made exception to removing _ ( like beijing_zh )
        return ( in_array( $cityName, $this->exceptionCityName ) ) ?
                $cityName :
                str_replace('_',' ', $cityName );
    }
}

class ImportExportedItemsException extends Exception{}