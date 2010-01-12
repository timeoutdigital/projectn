<?php

//require_once dirname( __FILE__ ) . '/../../lib/vendor/symfony/lib/config/sfConfigHandler.class.php';
//require_once dirname( __FILE__ ) . '/../../lib/vendor/symfony/lib/config/sfYamlConfigHandler.class.php';

//create a 'test' configured contenxt
//require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
//$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', false );

//sfContext::createInstance($configuration); //no need to dispatch the context. It'll just print loads of html into our output
//now class autoloading should be working

require_once( dirname(__FILE__).'/../../lib/vendor/symfony/lib/plugins/sfDoctrinePlugin/lib/vendor/doctrine/Doctrine.php');
spl_autoload_register( array( 'Doctrine', 'autoload' ) );

require_once( dirname( __FILE__ ) . '/../../lib/vendor/symfony/lib/utils/sfCoreAutoLoader.php' );
?>