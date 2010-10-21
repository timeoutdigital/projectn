<?php

/**
 * Check backups files have been stored correctly
 *
 * @package projectn
 * @subpackage task
 *
 * @author Rajeevan Kumarathasan
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * This script is intended to be run on dev #1.
 * It checks the backups folders to confirm that they were succesfully backed-up.
 *
 */

class importBoundariesCheckTask extends nagiosTask
{
    /**
     * Set Database connection to true
     * @var boolean
     */
    protected $enableDB     = true;
    /**
     * Set String description for this TASK
     * @var string
     */
    protected $description  = 'Check todays import agains lower import boundaries';

    /**
     * Override to baseclass configure functiom, this will add the additional option --yml to CLi interface
     * --yml will be used to override the default YAML configuration file for city default data
     */
    protected function  configure() {

        $this->addOption( 'yml', null, sfCommandOption::PARAMETER_OPTIONAL, null );
        parent::configure();
    }
    
    /**
     * created from abstract, this function will be called by the base class when thsi task executed
     * @param array $arguments
     * @param array $options
     */
    protected function executeNagiosTask( $arguments = array(), $options = array() )
    {
        $importBoundaryCheck = new importBoundariesCheck( $options );
        $errorMessages =  $importBoundaryCheck->getErrors();
        $this->setError( $errorMessages ); // add errors to Baseclass errors
    }
}