<?php

/**
 * dashboard actions.
 *
 * @package    sf_sandbox
 * @subpackage dashboard
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class dashboardActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
      // get vendor
      $vendors = Doctrine::getTable( 'Vendor' )->findAllVendorsInAlphabeticalOrder();
      $this->vendors = $vendors;
      
      // Get the Threshold
      $this->thresholdImport = array();
      $this->thresholdExport = array();
      
      // Import stats
      $this->importYesterday = new importBoundariesCheck( array('daysToAnalyse' => 1, 'type' => importBoundariesCheck::IMPORT ) ); // = yesterday and Today
      $this->importWeek = new importBoundariesCheck( array('daysToAnalyse' => 7, 'type' => importBoundariesCheck::IMPORT ) ); // = Week
      $this->importMonth = new importBoundariesCheck( array('daysToAnalyse' => 28, 'type' => importBoundariesCheck::IMPORT ) ); // = month

      // get the export stats
      $this->exportYesterday = new importBoundariesCheck( array('daysToAnalyse' => 1, 'type' => importBoundariesCheck::EXPORT ) ); // = yesterday and Today
      $this->exportWeek = new importBoundariesCheck( array('daysToAnalyse' => 7, 'type' => importBoundariesCheck::EXPORT ) ); // = week
      $this->exportMonth =  new importBoundariesCheck( array('daysToAnalyse' => 28, 'type' => importBoundariesCheck::EXPORT ) ); // = Month
      
  }
}
