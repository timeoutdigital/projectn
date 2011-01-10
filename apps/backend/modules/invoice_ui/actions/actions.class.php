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
    $this->date = new dateRangeSelectForm();
  }

  public function executeGenerateReport(sfWebRequest $request)
  {
      $dateFrom = sprintf('?/?/?', $request->getParameter( 'from_day' ), $request->getParameter( 'from_month' ), $request->getParameter( 'from_year' ) );
      $dateTo = sprintf('?/?/?', $request->getParameter( 'to_day' ), $request->getParameter( 'to_month' ), $request->getParameter( 'to_year' ) );
      $vendor_ID = $request->getParameter( 'vendor' );


      // Get from Database
      $results = Doctrine::getTable( 'ExportedItem' )->fetchBy( $dateFrom, $dateTo, $vendor_ID, 'poi' );

     return $this->renderText( 'found: ' . $results->count() );
     
  }
}
