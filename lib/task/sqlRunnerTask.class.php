<?php

class sqlRunnerTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('scripts', null, sfCommandOption::PARAMETER_REQUIRED, 'The script(s) you want to execute' ),
      new sfCommandOption('scriptsFolder', null, sfCommandOption::PARAMETER_OPTIONAL, 'sql script folder', 'scripts/sql'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'SQL Runner';
    $this->briefDescription = 'executes a raw sql file with pdo';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {

    if ( empty( $options['scripts'] ) ) throw new Exception( 'Please specify your sql script(s)' );

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $path = sfConfig::get( 'sf_root_dir' ) . '/' . $options['scriptsFolder'];

    $sqlFilesToProcess = DirectoryIteratorN::iterate( $path, DirectoryIteratorN::DIR_FILES, 'sql', $options['scripts'] . '_', true );

    $connection->beginTransaction();

    $this->writeLogLine( 'transaction started' );

    $i=0;
    
    foreach ( $sqlFilesToProcess as $file )
    {
        $i++;
        
        try {
            $query = file_get_contents( $file );

            $statement = $connection->prepare( $query );
            $statement->execute();
            $this->writeLogLine( 'executed: ' . $options['scripts'] . ' query ' . $i );
        }
        catch( Exception $e )
        {
            $connection->rollBack();
            $this->writeLogLine( 'transaction rolled back' );
            throw $e;
        }
    }

    $connection->commit();
    $this->writeLogLine( 'transaction commited' );
  }

  /* this is protected and not private in order to be overritten in the test, not ideal, but yeah */
  protected function writeLogLine( $message )
  {
      echo PHP_EOL . date( 'Y-m-d H:i:s' ) . ' -- ' . $message . ' -- ' . PHP_EOL;
  }
}
