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
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
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
    
    //Connect to the database.
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

   
    //Select the task
    switch($options['city'])
    {
      case 'ny':
      case 'NY':  $vendorObj = $this->getVendorByCityAndLanguage('ny', 'english');

                  $processXmlObj = new processXml('import/tony_leo.xml');

                  //Set the events and venues xpath
                  $processXmlObj->setEvents('/body/event')->setVenues('/body/address');

                  $nyImportObj = new importNy($processXmlObj, $vendorObjndorId);
                  $nyImportObj->insertEventsAndVenues();
                 
        break;

    }

  }


  
  /**
   * Get the Vendor by its city and language
   *
   * @param string $city
   * @param string $language
   *
   * @return object Result
   */
  private function getVendorByCityAndLanguage($city, $language)
  {
    $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage($city, $language);

    //Set the new vendor if one doens't exist
    if(!$vendorObj){
      $vendorObj = new Vendor();
      $vendorObj->setCity($city);
      $vendorObj->setLanguage($language);

      $vendorObj->save();

    }

    return $vendorObj;
   
  }
}
