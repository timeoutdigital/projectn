<?php

class reportDateOfFirstExportTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
          new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'poi | event | movie'),
          new sfCommandOption('pathToExports', null, sfCommandOption::PARAMETER_REQUIRED, 'path to the dated export folders'),
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'first-export-dates';
        $this->briefDescription = 'outputs dates of when a city xml was first updated.';
        $this->detailedDescription = <<<EOF
'outputs dates of when a city xml was first updated.'

[php symfony |INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        $finder = new exportFirstDateFinder( $options[ 'type' ], $options[ 'pathToExports' ] );
        foreach( $finder->run() as $city => $datetime )
        {
          echo $city . ': ' . $datetime->format( 'Y-m-d' ) . PHP_EOL;
        }
    }
}
