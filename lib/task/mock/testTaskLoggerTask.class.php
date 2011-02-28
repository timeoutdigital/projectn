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
    $this->_disableXdebug(); // disable xdebug
  }

  protected function execute($arguments = array(), $options = array())
  {
    taskLogger::start( $this, __FILE__, $options );

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
        case 'mixed':
            taskLogger::log( $this, 'single line log message' );
            trigger_error('triggered error', E_USER_WARNING );
            taskLogger::log( $this, 'single line log message' );
            taskLogger::log( $this, 'single line log message' );
            taskLogger::log( $this, 'single line log message' );
            for ( $i=0; $i < 20; $i++ )
            {
                taskLogger::log( $this, 'single line log message' );
                taskLogger::log( $this, 'multi line\nlog\n message' );
            }
            trigger_error('triggered error', E_USER_NOTICE );
            taskLogger::log( $this, 'multi line\nlog\n message' );
            taskLogger::log( $this, 'single line log message' );
            taskLogger::log( $this, 'multi line\nlog\n message' );
            taskLogger::log( $this, 'multi line\nlog\n message' );
            break;
        default:
            // just empty start/end outputed
    }

    taskLogger::end( $this );

  }

  private function _disableXdebug()
  {
      //#929 - xdebug is enabled by default in our Dev server 1, this cause this task to fail...
      //This will / should only for this task and not the whole test as CI server depends on xdebug data
      if(function_exists( 'xdebug_disable' ) )
      {
          xdebug_disable();
      }
  }

}
