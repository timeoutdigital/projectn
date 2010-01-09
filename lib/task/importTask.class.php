<?php

class importTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'Type of file to parse e.g. xml, csv'),
      //new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      //new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'import';
    $this->briefDescription = 'Import data files from vendors';
    $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php ./symfony nokia:import --file-type=xml|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
 
    switch($options['city'])
    {
      case 'ny':
      case 'NY':  $processXmlObj = new processXml('import/tony_leo.xml');

                  //Set the events and venues xpath
                  $processXmlObj->setEvents('/body/event')->setVenues('/body/address');


                  $nyImportObj = new importNy($processXmlObj);



        break;




      
    }


     //  echo count($processXmlObj->getVenues());
    /*foreach($locations as $location)
    {
        echo "{$location->identifier} \n";
    }

    foreach($events as $event)
    {
        echo "{$event->identifier} \n";
    }

     echo "\n $eventTotal";
*/
    // initialize the database connection
    //$databaseManager = new sfDatabaseManager($this->configuration);
    //$connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // add your code here
  }
}
