<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class fixIndiaGeoCodeTask extends sfBaseTask
{
    protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'fix-india-geocode';
    $this->briefDescription = 'Set all india geocode to null';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
      //Connect to the database.
      $databaseManager = new sfDatabaseManager($this->configuration);
      Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
      $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

      $vendorIds = array( 19 ,20,21,24 ); //india cities

      foreach ($vendorIds as $vendorId)
      {
          $pois = Doctrine::getTable('Poi')->findByVendorId( $vendorId );

          foreach($pois as $poi)
          {
              try{
                  $poi['geocode_look_up'] = $poi['latitude'] = $poi['longitude'] = null; // Empty existing data
                  $poi->save(); // save them
              }catch(Exception $exception)
              {
                  print_r('Exception caught in fix-india-geocode : ' . $exception->getMessage() . PHP_EOL);
              }
              break; // test
          }

        }
  }
}

?>
