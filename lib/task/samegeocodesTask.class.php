<?php

class samegeocodesTask extends sfBaseTask
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
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'fix-duplicate-geocodes';
    $this->briefDescription = 'Fix pois with duplicate geo codes';
    $this->detailedDescription = <<<EOF
The [same-geocodes|INFO] task does things.
Call it with:

  [php symfony same-geocodes|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();


    //Get pois grouped by the same lat/long

    echo "Total to fix: ".count($poiGroupObj);
    $count = count($poiGroupObj);

        //Loop through each group
        foreach($poiGroupObj as $poiGroup)
        {
            echo "Total Remaining ". $count . "\n";
            echo "Current Long: ".$poiGroup['longitude'] .' / '. $poiGroup['latitude'] . "\n";

            if(!is_null($poiGroup['longitude']) && !is_null($poiGroup['latitude']))
            {
                //Get all of the Poi's in this group
                $poiObj = Doctrine::getTable('Poi')->findbyLongitudeAndLatitude($poiGroup['longitude'], $poiGroup['latitude']);
print_r($poiObj->count());
                //Go through each POI and update the long/lat
                foreach($poiObj as $poi)
                {
                    $poi['longitude'] = null;
                    $poi['latitude']  = null;
                    $poi->setgeocoderLookUpString($poi['street'].', '.$poi['city']);
                    $poi->save();
                    print_r($poi->toArray());

                }
                exit;
            }


            $count--;

        }//end foreach
  }
}
