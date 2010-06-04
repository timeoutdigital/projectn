<?php

class resavePoisTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),

      new sfCommandOption('city', 'null', sfCommandOption::PARAMETER_OPTIONAL, 'The city to reload/save', ''),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'resavePois';
    $this->briefDescription = 'Loads and resaves all POIs';
    $this->detailedDescription = <<<EOF
The [resavePois|INFO] task loads each POI in turn and saves. This task was written to refresh street names with trailing spaces and commas.

Call it with:

  [php symfony resavePois|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // add your code here
    $cities = array( 'ny' => 1, 'chicago' => 2, 'singapore' => 3, 'lisbon' => 7, 'moscow' => 10, 'saint petersburg' => 11, 'omsk' => 12,
                     'almaty' => 13, 'novosibirsk' => 14, 'krasnoyarsk' => 15, 'tyumen' => 16, 'london' => 4, 'sydney' => 8,
                     'kuala lumpur' => 9, );

    $query = Doctrine::getTable( 'Poi' )->createQuery( 'p' );
    
    if ( isset( $options['city'] ) && $options['city'] && isset( $cities[ strtolower( $options['city'] ) ] ) )
        $query->where( 'p.vendor_id = ?', $cities[ strtolower( $options['city'] ) ] );

    $pois = $query->execute();

    $counter = 0;
    $total = count( $pois );
    
    foreach ( $pois as $poi )
    {
        $poi->save();

/*        if ( $counter++ % 100 == 0 )
            print "$counter / $total completed\n";  */
    }

    print "Done\n";
  }
}
