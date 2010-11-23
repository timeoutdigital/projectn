<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  static protected $htmlPurifierLoaded = false;
  
  public function setup()
  {
    $this->enablePlugins('sfDoctrinePlugin');
    //$this->enablePlugins('tmCsvPlugin');
    $this->enablePlugins('toLondonPlugin');
    $this->enablePlugins('sfDoctrineGuardPlugin');
    $this->enablePlugins('sfFormExtraPlugin');
    $this->enablePlugins('sfJqueryReloadedPlugin');
    $this->enablePlugins('projectnDashboardPlugin');

    sfConfig::set( 'projectn_xslt_dir', sfConfig::get( 'sf_data_dir' ) . '/xslt' );
  }

  static public function registerHTMLPurifier()
  {
    if(self::$htmlPurifierLoaded)
      return;

    require_once sfConfig::get('sf_lib_dir').'/vendor/htmlpurifier-library/HTMLPurifier/Bootstrap.php';
    spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));

    self::$htmlPurifierLoaded = true;
  }

  // Register Custom Hydrator
  public function configureDoctrine (Doctrine_Manager $manager)
  {
      $manager->registerHydrator( 'KeyValue', 'Doctrine_Hydrator_KeyValue' );
  }


}
