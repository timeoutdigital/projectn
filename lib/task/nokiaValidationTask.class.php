<?php

class nokiaValidationTask extends sfBaseTask
{
    const NOKIA_VALIDATOR_UPLOAD_URL = "http://ics-master.msudev.noklab.net/self-validation/upload/";

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

        $folders = DirectoryIteratorN::iterate( $baseDir, DirectoryIteratorN::DIR_FOLDERS );

        sort( $folders );

        foreach( $folders as $folder )
            if( strpos( $folder, "export_" ) !== false )
                $latestExportFolder = $folder;

        if( !isset( $latestExportFolder ) ) throw new Exception( "Could not find latest export folder." );

        $validationDir = $baseDir . str_replace( "export", "validation", $latestExportFolder ) . "/";

        $modelFolder = $baseDir . $latestExportFolder . "/poi/";

        $poiFolder = DirectoryIteratorN::iterate( $modelFolder, DirectoryIteratorN::DIR_FILES, "xml" );

        foreach( $poiFolder as $file )
            $this->validate( 'poi', $modelFolder . $file, "moo" );
    }

    protected function validate( $type, $inputFile, $outputFile )
    {
        if( !is_readable( $inputFile ) )
            throw new Exception( "Cannot Open File For Upload." );

        echo "CURL: " . $inputFile . PHP_EOL;

        $parameters = array();
        $parameters['mptest'] = "@" . $inputFile . ";type=text/xml";
        //$parameters['submit'] = 'Upload';

        $c = curl_init();

        curl_setopt( $c , CURLOPT_URL, self::NOKIA_VALIDATOR_UPLOAD_URL . strtolower( $type ) );
        curl_setopt( $c , CURLOPT_HEADER, true );
        curl_setopt( $c , CURLOPT_VERBOSE, true );
        curl_setopt( $c , CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $c , CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.2.7) Gecko/20100715 Ubuntu/9.10 (karmic) Firefox/3.6.7' );
        curl_setopt( $c , CURLOPT_REFERER, 'http://ics-master.msudev.noklab.net/self-validation/' );
        //curl_setopt( $c , CURLOPT_COOKIE, 'JSESSIONID=ED1A493B4015742E36632EAECEDCB374' );

        //curl_setopt( $c , CURLOPT_HTTPAUTH, CURLAUTH_ANY );
        //curl_setopt( $c , CURLOPT_SSL_VERIFYPEER, false );
        //curl_setopt( $c , CURLOPT_SSL_VERIFYHOST, false );
        //curl_setopt( $c , CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $c , CURLOPT_POST, true );
        curl_setopt( $c , CURLOPT_POSTFIELDS, $parameters );
        curl_setopt( $c , CURLOPT_INFILESIZE, filesize( $inputFile ) );



//        curl_setopt( $c, CURLOPT_PROGRESSFUNCTION, 'onProgress' );
//        
//        function onProgress($download_size, $downloaded, $upload_size, $uploaded)
//        {
//            // do your progress stuff here
//        }

        $r = curl_exec( $c );

        if( curl_errno($c) )
        {
            print curl_error($c);
            print "<br>Unable to upload file.";
            exit();
        }

        print_r( curl_getinfo( $c ) );

        var_dump( $r );
    }
}