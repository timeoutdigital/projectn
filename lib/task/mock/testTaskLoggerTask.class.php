<?php

class testTaskLoggerTask extends sfBaseTask
{

  protected $config;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type of the test to execute'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'Switch on/off printing of log info'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('configFolder', null, sfCommandOption::PARAMETER_OPTIONAL, 'The config file to be used (if other than default)'),
    ));

    $this->namespace        = 'projectn-mock';
    $this->name             = 'testTaskLogger';
    $this->briefDescription = 'Test runs for the taskLogger';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {

    taskLogger::start( $this, $options );
    
    switch( $options['type'] )
    {
        case 'success':
            taskLogger::log( $this, 'single line log message' );
            taskLogger::log( $this, 'multi line\nlog\n message' );
            break;
        case 'error-notice':
            trigger_error('triggered error', E_USER_NOTICE );
            break;
        case 'error-warning':
            trigger_error('triggered error', E_USER_WARNING );
            break;
        case 'error-error':
            trigger_error('triggered error', E_USER_ERROR );
            break;

        default:
            // just empty start/end outputed
    }

    taskLogger::end( $this );

  }

}
