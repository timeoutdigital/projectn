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
      $vendors = Doctrine::getTable( 'Vendor' )->findAll();
      $this->vendors = $vendors;
      
      // Get the Threshold
      $importBoundary = new importBoundariesCheck( );
      $this->thresholdImport = array();
      $this->thresholdImport['yesterday'] = $importBoundary->getPercentageDiffByXDaysForImport( 1 ); // = yesterday and Today
      $this->thresholdImport['week'] = $importBoundary->getPercentageDiffByXDaysForImport( 7 ); // = Week
      $this->thresholdImport['month'] = $importBoundary->getPercentageDiffByXDaysForImport( 28 ); // = Month

      // get the export stats
      $this->thresholdExport = array();
      $this->thresholdExport['yesterday'] = $importBoundary->getPercentageDiffByXDaysForExport( 1 ); // = yesterday and Today
      $this->thresholdExport['week'] = $importBoundary->getPercentageDiffByXDaysForExport( 7 ); // = Week
      $this->thresholdExport['month'] = $importBoundary->getPercentageDiffByXDaysForExport( 28 ); // = Month
  }
}
