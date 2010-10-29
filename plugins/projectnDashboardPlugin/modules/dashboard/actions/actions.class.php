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

      // Get the Threshold
      $importBoundary = new importBoundariesCheck( );
      $yesterdayData = $importBoundary->getPrcentageDiffByXDays( 1 ); // = yesterday and Today
      $weekData = $importBoundary->getPrcentageDiffByXDays( 7 ); // = Week
      $monthData = $importBoundary->getPrcentageDiffByXDays( 28 ); // = Month

      // Send Data to View
      $this->vendors = $vendors;
      $this->yesterdayData = $yesterdayData;
      $this->weekData = $weekData;
      $this->monthData = $monthData;

  }
}
