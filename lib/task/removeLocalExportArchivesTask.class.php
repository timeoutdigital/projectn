<?php

class removeLocalExportArchivesTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('simulate', null, sfCommandOption::PARAMETER_OPTIONAL, 'set --simulate=true to Skip actully deleting file', null),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'removeLocalExportArchives';
    $this->briefDescription = 'Remove Locally archived export files when they are found on s3';
    $this->detailedDescription = <<<EOF
The [removeLocalExportArchives|INFO] task to free local disk space when they are found to be exists on Amazon S3
user --simulate to Skip deleting file, and output file name to be deleted
  [php symfony removeLocalExportArchives|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // add your code here
    $s3cmd = new s3cmd();
    $exports_in_amazon = $s3cmd->getListOfExportArchives();
    
    foreach( DirectoryIteratorN::iterate( sfConfig::get( 'projectn_export' ), DirectoryIteratorN::DIR_FILES, 'tgz', 'exports_', false) as $file )
    {
        // Only need to go through files older than 6 week
        if( $this->_extractTimeStamp( $file ) >= strtotime( '-6 week' ) )
        {
            continue; // ensure that we don't delete any tiles that fall within 6 week.
        }

        // check this file exists in Amazon S3 or Echo a message
        if( !array_key_exists( $file, $exports_in_amazon ) )
        {
            $this->logSection('File Exists', "File {$file} not found on Amazon s3", null , 'ERROR' );
            continue;
        }

        // check MD5 match
        $real_file_path = sfConfig::get('projectn_export') . DS . $file;
        $file_md5 = md5_file( $real_file_path );
        if( $file_md5 !== $exports_in_amazon[ $file ] )
        {
            $this->logSection('MD5 CHECK', "{$file} MD5 SUM don't match. [Local: {$file_md5}] [S3: {$exports_in_amazon[ $file ]}]", null , 'ERROR' );
            continue;
        }

        if( $options['simulate'] !== null )
        {
            $this->logSection( 'DELETE', "Deleting file {$file}" );
        }
        else // DELETE the actual FILE from Local DISK!
        {
            if( !unlink( $real_file_path ) )
            {
                $this->logSection( 'DELETE', "Failed to delete file {$real_file_path}", null , 'ERROR' );
            } // Well, there goes disk space nagios 3 in hte morning :)
        }
    }
    
  }

  private function _extractTimeStamp( $fileName )
  {
      $fileName = str_replace( array('exports_', '.tgz' ), '', $fileName );
      
      return strtotime( $fileName );
  }
}
