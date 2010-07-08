<?php

class uploadTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev')
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'upload';
    $this->briefDescription = '';
    $this->detailedDescription =  '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $userName = 'timeout';
    $password = 't1M0T#8';
    $host     = 'pictis.msudev.noklab.net';

    $config = $this->createConfiguration( 'backend', $options[ 'env' ] );

    // get the doctrine database connection
    $manager = new sfDatabaseManager($this->configuration);

    $this->logSection( 'Upload' ,'started at ' .date( "Y-m-d H:i:s" ) );

    $DS = DIRECTORY_SEPARATOR;

    $exportDir = sfConfig::get( 'sf_root_dir' ) . $DS . 'export' . $DS .  'export_' . date( "Ymd" ) .$DS ;

    $items = array( 'movie' , 'poi' , 'event' );

    foreach ($items as $item)
    {
        $cmd = 'zip ' . $exportDir . $item . $DS. $item . '.zip ' . $exportDir   . $item . '/*';

        $this->exec( $cmd , 'create zip file for ' . $item );

        $cmd = 'md5sum ' . $exportDir . $item . $DS. $item . '.zip > ' . $exportDir . $item  . $DS. $item .  '.zip.md5';

        $this->exec( $cmd , 'create md5 file for ' . $item  );
    }

    //upload lock

    $cmd = "lftp -c 'open -e \"put {$exportDir}upload.lock\" -u {$userName},{$password} {$host}'";

    $this->exec( $cmd , 'upload lock' );

    //removing remote files
    foreach ($items as $item)
    {
        $cmd = "lftp -c 'open -e \"rm {$item}/$item.zip\" -u {$userName},{$password} {$host}'";

        $this->exec( $cmd , 'Remove remote files : ' . $item . '.zip' );

        $cmd = "lftp -c 'open -e \"rm {$item}/$item.zip.md5\" -u {$userName},{$password} {$host}'";

        $this->exec( $cmd , 'Remove remote files : ' . $item . '.zip.md5' );

    }

    //sync files
    $cmd = "lftp -c 'open -e \"mirror -R -x xml {$exportDir} /\" -u {$userName},{$password} {$host}'";

    $this->exec( $cmd , 'sync files' );

    // remove lock file
    $cmd = "lftp -c 'open -e \"rm upload.lock\" -u {$userName},{$password} {$host}'";

    $this->exec( $cmd , 'remove lock file' );

    $this->logSection( 'Upload' ,' finished at ' .date( "Y-m-d H:i:s" ) );

  }

  private function exec( $cmd ,$section )
  {
      $results = array();

      exec( $cmd, $results );

      $this->logSection( $section , date( "H:i:s" ) );

      foreach ( $results as $result )
      {
        $this->logSection( $section ,  trim( $result ) );
      }
  }

}
