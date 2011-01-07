<?php

class importExportedItemsTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
            new sfCommandOption('exportdir', null, sfCommandOption::PARAMETER_REQUIRED, 'Give Full path for Export folder(where export_yyyymmdd folder resides)', null),
            new sfCommandOption('date', null, sfCommandOption::PARAMETER_OPTIONAL, 'Specify export date (yyyymmdd) to limit to specific date',null),
            new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'Model (poi, event, movie) to Limit to specific model only',null),

        ));

        $this->namespace        = 'projectn';
        $this->name             = 'importExportedItems';
        $this->briefDescription = '';
        $this->detailedDescription =  '';
    }

    protected function execute($arguments = array(), $options = array())
    {
        if( !is_string($options['exportdir']) )
        {
            throw new Exception( "Invalid Export Dir, use --exportdir='full/path/to/export'" );
        }

        $allowedOptions = array( 'poi', 'event', 'movie' );
        if( $options[ 'model' ] != null && !in_array( strtolower( $options[ 'model' ] ), $allowedOptions ) )
        {
            throw new Exception( 'Invalid model given! use --model=' . implode('/',$allowedOptions) );
        }

        // Extabilish database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        if( isset( $options['date'] ) && is_string( $options['date'] ) )
        {
            $fullDateExportPath = $options['exportdir'] . '/export_' . $options['date'];
            if( !is_dir( $fullDateExportPath ) )
            {
                throw new Exception( 'Specific date export dir not found ('.$fullDateExportPath.')' );
            }

            // invoke import of export
            $this->import1DayExport( $fullDateExportPath );
            return; // The End
        }

        // Import using Directory Iterator
        $exportDirs = DirectoryIteratorN::iterate( $options['exportdir'], DirectoryIteratorN::DIR_FOLDERS,'','export_' , true);
        if( !is_array( $exportDirs ) || empty( $exportDirs ) )
        {
            throw new Exception( 'No Export DIR found in given Export path ( '.$options['exportdir'].')' );
        }

        foreach($exportDirs as $exportDateDir)
        {
            $this->import1DayExport( $exportDateDir );
        }
    }

    private function import1DayExport( $exportDayFullPath )
    {
        $importExported = new importExportedItems( $exportDayFullPath );
        $importExported->import();
    }

}
