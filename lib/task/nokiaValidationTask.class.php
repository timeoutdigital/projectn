<?php

class nokiaValidationTask extends sfBaseTask
{
    const NOKIA_VALIDATOR_UPLOAD_URL = "http://ics-master.msudev.noklab.net/self-validation/validate/";

    protected function configure()
    {
        $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'nokia-validation';
        $this->briefDescription = 'Validate exports on Nokia Server';
        $this->detailedDescription = <<<EOF
The [nokia-validation|INFO] task does things.
Call it with:

[php symfony nokia-validation|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $baseDir = "/n/export/";
        chdir( $baseDir );

        $folders = DirectoryIteratorN::iterate( $baseDir, DirectoryIteratorN::DIR_FOLDERS );

        foreach( $folders as $folder )
            if( strpos( $folder, "export_" ) !== false )
                $latestExportFolder = $folder;

        if( !isset( $latestExportFolder ) )
            throw new Exception( "Could not find latest export folder." );


        $validationBaseDir = str_replace( "export_", "validation_", $latestExportFolder ) . "/";
        is_dir( $validationBaseDir ) || mkdir( $validationBaseDir );
        file_exists( $validationBaseDir . "digest.txt" ) || touch( $validationBaseDir . "digest.txt" );
        $handle = fopen( $validationBaseDir . "digest.txt", "w" ); fclose( $handle ); // Wipe Digest File.

        $modelsFolders = DirectoryIteratorN::iterate( $latestExportFolder, DirectoryIteratorN::DIR_FOLDERS );

        foreach( $modelsFolders as $modelType )
        {
            $modelDir = $latestExportFolder . "/$modelType/";
            $modelFiles = DirectoryIteratorN::iterate( $modelDir, DirectoryIteratorN::DIR_FILES, "xml" );

            $validationModelDir = str_replace( "export_", "validation_", $modelDir );

            is_dir( $validationModelDir ) || mkdir( $validationModelDir );
            
            foreach( $modelFiles as $file )
                $this->validate( $modelType, $modelDir . $file, $validationModelDir . str_replace( ".xml", ".txt", $file ), $validationBaseDir . "digest.txt" );
        }
    }

    protected function validate( $type, $inputFile, $outputFile, $digestFile )
    {
        if( !is_readable( $inputFile ) )
            throw new Exception( "Cannot Open File For Upload." );

        $cmd = "curl -N -F 'file=@$inputFile;type=text/xml' -# '". self::NOKIA_VALIDATOR_UPLOAD_URL . strtolower( $type ) ."' -o $outputFile";
        $resp = shell_exec( $cmd );

        // -- Write Digest --
        
        $failedValidation = shell_exec( "grep 'does not validate against schema' $outputFile" );
        $errorSummary = shell_exec( "grep 'Error type:' $outputFile | grep -v ': 0'" );

        if( !is_null( $failedValidation ) || !is_null( $errorSummary ) )
        {
            $digest = str_pad( " " . $inputFile . " ", 100, "-", STR_PAD_BOTH ) . PHP_EOL . PHP_EOL;
            if( !is_null( $failedValidation ) ) $digest .= trim( $failedValidation ) . PHP_EOL;
            if( !is_null( $errorSummary ) ) $digest .= trim( $errorSummary ) . PHP_EOL;
            file_put_contents( $digestFile, PHP_EOL . $digest, FILE_APPEND );
        }

        return $resp;
    }
}