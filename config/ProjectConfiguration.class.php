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
  }

  static public function registerHTMLPurifier()
  {
    if(self::$htmlPurifierLoaded)
      return;

    require_once sfConfig::get('sf_lib_dir').'/vendor/htmlpurifier-library/HTMLPurifier/Bootstrap.php';
    spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));

    self::$htmlPurifierLoaded = true;
  }
}
