<?php

class downloadMediaTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
          new sfCommandOption('delimiter', null, sfCommandOption::PARAMETER_REQUIRED, 'Delimeter', "\n" ),
          new sfCommandOption('identfile', null, sfCommandOption::PARAMETER_REQUIRED, 'Delimeted file of media idents', 'identfile.txt'),
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'download-media';
        $this->briefDescription = 'Download Media Files';
        $this->detailedDescription = <<<EOF
The [download-media|INFO] task parses a elimeted file of media idents and re-downloads the files.
Call it with:

[php symfony download-media|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        $identArray = $this->parseIdentFile( $options );
        $this->curlMedia( $options, $identArray );
    }

    protected function parseIdentFile( $options )
    {
        if( !file_exists( $options[ 'identfile' ] ) ) throw new Exception( "Ident File Not Found." );

        $f = trim( file_get_contents( $options[ 'identfile' ] ) );

        if( strpos( $f, $options[ 'delimiter' ] ) === false ) throw new Exception( "Ident File Does Not Contain Delimeter." );

        return $f;
    }

    protected function curlMedia( $options, $identArray )
    {
        $failures = array();

        foreach( explode( $options[ 'delimiter' ] , $identArray ) as $ident )
        {
            try {
                $m = $this->findMediaInDb( $ident );
                if( $m === false ) throw new Exception( "Ident '$ident' not Found in DB." );

                $parentClass = str_replace( "Media", "", get_class( $m ) );
                $mediaVendor = $m[ $parentClass ]['Vendor'];
                
                $m->populateByUrl( $m['ident'], $m['url'], $mediaVendor['city'] );
            }

            catch( Exception $e )
            {
                $failures[ $ident ] = $e->getMessage();
            }
        }

        if( !empty( $failures ) )
        {
            echo "A Total of " . count( $failures ) . " Media Files Failed to Download." . PHP_EOL;
            print_r( $failures );
        }

        echo "Successfully Downloaded " . ( count( $identArray ) - count( $failures ) ) . " Media Files." . PHP_EOL;
    }

    protected function findMediaInDb( $ident )
    {
        $media = Doctrine::getTable('PoiMedia')->findOneByIdent( $ident );
        if( $media !== false ) return $media;

        $media = Doctrine::getTable('EventMedia')->findOneByIdent( $ident );
        if( $media !== false ) return $media;

        $media = Doctrine::getTable('MovieMedia')->findOneByIdent( $ident );
        if( $media !== false ) return $media;

        return false;
    }
}