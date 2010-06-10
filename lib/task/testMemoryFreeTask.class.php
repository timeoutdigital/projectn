<?php

class testMemoryFreeTask extends sfBaseTask
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
    $this->name             = 'test-memory-free-task';
    $this->briefDescription = 'Test freeing of memory for Doctrine objects.';
    $this->detailedDescription = <<<EOF
The [same-geocodes|INFO] task does things.
Call it with:

  [php symfony test-memory-free-task|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    echo "Memory Usage: " . $this->convert( memory_get_usage() ) . PHP_EOL;

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $time = 500;
    $sleep = 1;
    $max_records = 10000;
    $record_type = "Poi";

    $total_records = Doctrine::getTable( $record_type )->count();

    $collections_array = array();

    for( $x=0; $x<$time; $x++ )
    {
        // Select $max_records random record ids
        $random_record_ids = array();
        for( $y=0; $y<$max_records; $y++ ) $random_record_ids[] = rand( 0, $total_records );

        // Get collection from db
        $collection = Doctrine::getTable( $record_type )->createQuery('r')
                ->where( "r.id IN (?)", implode( ",", $random_record_ids ))
                ->limit( $max_records )
                ->execute();

        // Echo Stuff
        echo "Memory Usage: " . $this->convert( memory_get_usage() ) . PHP_EOL;

        $system_wide_references_to_objects[] = $collection;
        
        // Try to free memory
        $this->free_memory( $collection );
        //unset( $collection );

        //echo get_class( $system_wide_references_to_objects[ $x ] ) . PHP_EOL;

        // Sleep a bit
        //time_nanosleep(0, (int) $sleep * 100000000 );
    }

    foreach( $system_wide_references_to_objects as $c )
        echo $c->count() . PHP_EOL;

  }

private function free_memory( $collection )
{
    $collection->free( true );
    unset( $collection );
    gc_collect_cycles();
}

private function convert($size)
 {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }
}