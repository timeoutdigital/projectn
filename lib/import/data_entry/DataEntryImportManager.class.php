<?php
class DataEntryImportManager
{

    private $importDir;

    private $vendor;



    public function __construct( $cityName, $importDir)
    {
        $this->vendor =  Doctrine::getTable( 'Vendor' )->findOneByCity( $cityName );
        
        $this->importDir = $importDir;
    }


    public function importPois()
    {
       $this->runImport( 'poi' );
    }

    public function importEvents()
    {
       $this->runImport( 'event' );
    }

    public function importMovies()
    {
       $this->runImport( 'movie' );
    }

    public function getFileList( $type )
    {
        $validTypes = array( 'poi' ,'event','movie' );

        if( !in_array( $type, $validTypes ) )
        {
            $this->notifyImporterOfFailure( new Exception( 'invalid item type for DataEntryImportManager::getFileList' ) );
            return;
        }

        $latestExportDir = $this->getLatestExportDir();

        $dir = $latestExportDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;

        $files = DirectoryIteratorN::iterate( $dir, DirectoryIteratorN::DIR_FILES, 'xml', '', true );

        return $files;
    }

    private function runImport( $type )
    {
        $validTypes = array( 'poi' ,'event','movie' );

        if( !in_array( $type, $validTypes ) )
        {
            $this->notifyImporterOfFailure(  new Exception( 'invalid item type for DataEntryImportManager::runImport' ) );
            return;
        }

        $files = $this->getFileList( $type );

        foreach ( $files as $file)
        {
            $importer = new Importer();

            $xml = simplexml_load_file ( $file );

            $cityName = basename( $file, ".xml" );

            $this->vendor =  Doctrine::getTable( 'Vendor' )->findOneByCity( $cityName );

            ImportLogger::getInstance()->setVendor( $this->vendor );

            switch ( $type)
            {
                case 'poi':
                    $importer->addDataMapper( new DataEntryPoisMapper( $xml, null, $cityName ) );
                    break;

                case 'event':
                     $importer->addDataMapper( new DataEntryEventsMapper( $xml, null, $cityName ) );
                     break;

                case 'movie':
                    $importer->addDataMapper( new DataEntryMoviesMapper( $xml, null, $cityName ) );
                    break;
            }

            $importer->run();
        }
    }

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
            if ($dh = opendir( $this->importDir ) )
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
             throw  new Exception( $this->importDir . ' is not a directory'  ) ;
             return;
        }
        sort( $subDirectories );

        return $this->importDir . 'export_'. end( $subDirectories );
    }
}
