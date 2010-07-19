<?php
class DataEntryImportManager
{
    private static $importDir = '/home/emre/export/';

    static public function importPois()
    {
       self::runImport( 'poi' );
    }

    static public function importEvents()
    {
       self::runImport( 'event' );
    }

    static public function importMovies()
    {
       self::runImport( 'movie' );
    }

    static private function runImport( $type )
    {
        $validTypes = array( 'poi' ,'event','movie' );

        if( !in_array( $type, $validTypes ) )
        {
            throw new Exception( 'invalid item type for DataEntryImportManager::getFileList' );
        }

        $files = self::getFileList( $type );

        foreach ( $files as $file)
        {
            $importer = new Importer();

            $xml = simplexml_load_file ( $file );

            $cityName = basename( $file, ".xml" );

            $vendorObj =  Doctrine::getTable( 'Vendor' )->findOneByCity( $cityName );

            ImportLogger::getInstance()->setVendor( $vendorObj );

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

    static public function setImportDir( $dir )
    {
        self::$importDir = $dir;
    }

    static private function getLatestExportDir()
    {
        $subDirectories = array();

        if ( is_dir( self::$importDir ) )
        {
            if ($dh = opendir( self::$importDir ) )
            {
                while (($file = readdir( $dh )) !== false)
                {
                    if( filetype( self::$importDir . $file) == 'dir' && strlen( $file ) > 3 )
                    {
                         //remove the export_ part to only get the dates
                         $subDirectories  [] = str_replace( 'export_' , '',$file );
                    }
                }
                closedir($dh);
            }
        }
         sort( $subDirectories );

         return self::$importDir . 'export_'. end( $subDirectories );
    }


    static private function getFileList( $type )
    {
        $validTypes = array( 'poi' ,'event','movie' );

        if( !in_array( $type, $validTypes ) )
        {
            throw new Exception( 'invalid item type for DataEntryImportManager::getFileList' );
        }

        $latestExportDir = self::getLatestExportDir();

        $dir = $latestExportDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;

        $files = array();

        if ( is_dir( $dir ) )
        {
            if ($dh = opendir( $dir ) )
            {
                while (($file = readdir( $dh )) !== false)
                {
                    if( filetype( $dir . $file) == 'file' && strpos( $file, '.xml' ) !== false)
                    {
                         $files [] = $dir .$file;
                    }
                }
                closedir($dh);
            }
        }

        return $files;
    }

}