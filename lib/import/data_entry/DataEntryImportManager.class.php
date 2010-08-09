<?php
class DataEntryImportManager
{

    /**
     * Import Dir Path
     * @var string
     */
    private $importDir;

    /**
     * Vendor Object
     * @var Doctrine_Record
     */
    private $vendor;


    public function __construct( $cityName, $importDir)
    {
        $this->vendor =  Doctrine::getTable( 'Vendor' )->findOneByCity( $cityName );

        if( !isset( $this->vendor ) || !$this->vendor )
        {
            throw new Exception( 'DataEntryImportManager : No Vendor Found for City - ' . $cityName ) ;
        }
        
        $this->importDir = $importDir;
    }


    /**
     * Import POI's
     */
    public function importPois()
    {
       $this->runImport( 'poi' );
    }

    /**
     * Import Events
     */
    public function importEvents()
    {
       $this->runImport( 'event' );
    }

    /**
     * Import Movie
     */
    public function importMovies()
    {
       $this->runImport( 'movie' );
    }

    /**
     * Run Import for specific TYPE
     * @param string Import Type [poi, event or movie]
     * @return null
     */
    private function runImport( $type )
    {
        $validTypes = array( 'poi' ,'event','movie' );

        if( !in_array( $type, $validTypes ) )
        {
            throw new Exception( 'invalid item type for DataEntryImportManager::runImport' );
            return;
        }
        // Check for Vendor
        if( !$this->vendor )
        {
            throw new Exception( 'DataEntryImportManager::runImport No Vendor Found for City - ' . $this->vendor['city'] );
            return;
        }

        // Get the Exported XML FILE
        $filePath = $this->getLatestExportDir() . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . str_replace(' ', '_', strtolower( $this->vendor['city'] ) ) . '.xml';

        // Check File Exists
        if( !file_exists( $filePath ) )
        {
            throw new Exception( 'DataEntryImportManager::runImport Export file for city ' . $this->vendor['city'] . ' is not found! Path location: ' . $filePath , 1);
            return;
        }

        // Start Import
        ImportLogger::getInstance()->setVendor( $this->vendor );
        $importer = new Importer();
        $xml = simplexml_load_file ( $filePath ); // Load XML

        switch ( $type)
        {
            case 'poi':
                $importer->addDataMapper( new DataEntryPoisMapper( $xml, null, $this->vendor['city'] ) );
                break;
            case 'event':
                $importer->addDataMapper( new DataEntryEventsMapper( $xml, null, $this->vendor['city'] ) );
                break;
            case 'movie':
                $importer->addDataMapper( new DataEntryMoviesMapper( $xml, null, $this->vendor['city'] ) );
                break;
            default:
                throw new Exception( 'DataEntryImportManager::runImport Invalid Type ' . $type ); return; // Exit
                break;
        }

        // Start Importer
        $importer->run();
    }

    /**
     *  get Latest Export Directory Path
     * @return String ExportFilePath
     */
    private function getLatestExportDir()
    {
        $subDirectories = array();

        if( is_null( $this->importDir ) )
        {
           //set it to the default
           $this->importDir = sfConfig::get('sf_root_dir') . '_data_entry' . DIRECTORY_SEPARATOR . 'export' .DIRECTORY_SEPARATOR;
        }

        if ( is_dir( $this->importDir ) )
        {
            $dh = opendir( $this->importDir );
            if ( $dh )
            {
                while (($file = readdir( $dh )) !== false)
                {
                    if( filetype( $this->importDir . $file) == 'dir' && strlen( $file ) > 3 && strpos( $file , 'export_' ) !== false )
                    {
                         //remove the export_ part to only get the dates
                         $subDirectories  [] = str_replace( 'export_' , '',$file );
                    }
                }
                closedir($dh);
            }
        }
        else
        {
             throw  new Exception( 'DataEntryImportManager::getLatestExportDir '. $this->importDir . ' is not a directory'  ) ;
             return;
        }
        sort( $subDirectories );

        return $this->importDir . 'export_'. end( $subDirectories );
    }
}
