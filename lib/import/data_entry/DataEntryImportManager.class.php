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

    const INSTALLATION_PROJECT_N = 'projectn';
    const INSTALLATION_PROJECT_N_DATA_ENTRY = 'projectn_data_entry';

    public function __construct( $cityName )
    {
        $this->vendor =  Doctrine::getTable( 'Vendor' )->findOneByCity( $cityName );

        if( !isset( $this->vendor ) || !$this->vendor )
        {
            throw new Exception( 'DataEntryImportManager : No Vendor Found for City - ' . $cityName ) ;
        }

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

        $this->importDir = $this->locateImportDir();

        if( is_null( $this->importDir )  || !is_dir($this->importDir ) )
        {
            throw new Exception( 'DataEntryImportManager couldnt locate the import directory' );
        }
        var_dump( $this->importDir );
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

    private function locateImportDir()
    {
        $sfRootDirectory = sfConfig::get( "sf_root_dir" );

        if( ( strpos( $sfRootDirectory , 'projectn_data_entry' ) === false)  )
        {
            $installation       = self::INSTALLATION_PROJECT_N ;
            $targetInstallation = self::INSTALLATION_PROJECT_N_DATA_ENTRY ;

        }
        else
        {
            $installation       = self::INSTALLATION_PROJECT_N_DATA_ENTRY ;
            $targetInstallation = self::INSTALLATION_PROJECT_N ;
        }

        exec( 'locate ' . $targetInstallation. '/config/databases.yml'  ,$output );

        $resultCount = count( $output );

        if( $resultCount == 1 )
        {
            $path = str_replace( '/config/databases.yml' , '', $output[ 0 ] );

        }else
        {
            return NULL;
        }

        if( $installation == self::INSTALLATION_PROJECT_N_DATA_ENTRY  )
        {
            $path = $path.'/export/data_entry/';
        }else
        {
            $path = $path. '/export/';
        }

        return $path;
    }

    public function getImportDir()
    {
        return $this->importDir;
    }

}
