<?php

class invoiceTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'invoice';
    $this->briefDescription = 'Produce invoice from exports';
    $this->detailedDescription = <<<EOF
The [invoice|INFO] task does things.
Call it with:

  [php symfony invoice|INFO]
EOF;

    define( 'DIR_ALL', 'all' );
    define( 'DIR_FILES', 'files' );
    define( 'DIR_FOLDERS', 'folders' );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $baseDir = "invoice-test/exports";

    $folders = $this->readDir( $baseDir, DIR_FOLDERS );
    print_r( $folders );
    
    $poiFiles = $this->readDir( $baseDir."/".$folders[0]."/poi", DIR_FILES );
    print_r( $poiFiles );

    $xml = simplexml_load_file( $baseDir."/".$folders[0]."/poi/".$poiFiles[0] );
    $totalPois = count( $xml->xpath( '/vendor-pois/entry' ) );

    
  }

  protected function readDir( $dir = ".", $which = DIR_ALL )
  {
    $filesArray = array();

    $path = realpath( $dir );
    if( $path === false || !is_dir( $path ) ) throw new Exception( "Folder Not Found '" . $dir . "'" );
    
    $d = dir( $path );

    while ( false !== ( $entry = @$d->read() ) )
    {
        if( $entry == '.' || $entry == '..' )
            continue;

        if( $which === DIR_FOLDERS )
        {
            if( is_dir( realpath( $d->path . "/" . $entry ) ) )
                $filesArray[] = $entry;
        }
                
        elseif( $which === DIR_FILES )
        {
            if( is_file( realpath( $d->path . "/" . $entry ) ) )
                $filesArray[] = $entry;
        }

        else $filesArray[] = $entry;
    }
        
    $d->close();

    return $filesArray;
  }
}