<?php

/**
 * invoice_ui actions.
 *
 * @package    sf_sandbox
 * @subpackage invoice_ui
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class invoice_uiActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
      $vendorList = Doctrine::getTable('Vendor')->findAll( 'KeyValue' );
      asort( $vendorList );
        
    $this->date = new filterOptionForm();
    $this->date->setVendorChoices( $vendorList );
  }

  public function executeGenerateReport(sfWebRequest $request)
  {
      $dateFrom = sprintf('%s/%s/%s', $request->getParameter( 'from_year' ), $request->getParameter( 'from_month' ), $request->getParameter( 'from_day' ) );
      $dateTo = sprintf('%s/%s/%s', $request->getParameter( 'to_year' ), $request->getParameter( 'to_month' ), $request->getParameter( 'to_day' ) );
      $vendor_ID = $request->getParameter( 'vendor' );
      $invoiceable = ($request->getParameter( 'invoiceable' ) == 'true') ? true : false;

      // Get from Database
      $results = Doctrine::getTable( 'ExportedItem' )->fetchBy( $dateFrom, $dateTo, $vendor_ID, 'poi', null, $invoiceable );

     return $this->renderText( 'found: ' .$results->count() . print_r($results->toArray(), true ) );
     
  }
}
