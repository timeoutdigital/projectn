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
      $this->dateFrom = sprintf('%s/%s/%s', $request->getParameter( 'from_year' ), $request->getParameter( 'from_month' ), $request->getParameter( 'from_day' ) );
      $this->dateTo  = sprintf('%s/%s/%s', $request->getParameter( 'to_year' ), $request->getParameter( 'to_month' ), $request->getParameter( 'to_day' ) );
      $this->model  = $request->getParameter( 'model' );

      $vendor_ID = $request->getParameter( 'vendor' );      
      $this->vendor = Doctrine::getTable( 'Vendor' )->find( $vendor_ID, Doctrine_Core::HYDRATE_ARRAY );
      $this->invoiceable = ($request->getParameter( 'invoiceable' ) == 'true') ? true:false;

      $invoiceableCategory = array();
      if( $this->invoiceable )
      {
          $invoiceableYaml = sfYaml::load( file_get_contents( sfConfig::get( 'sf_config_dir' ) . '/invoiceableCategory.yml' ) );
          $invoiceableCategory = array_keys( $invoiceableYaml[ $this->model ] );
      }

      // Get from Database
      $results = Doctrine::getTable( 'ExportedItem' )->fetchBy( $this->dateFrom, $this->dateTo, $vendor_ID, $this->model, $invoiceableCategory, null, $this->invoiceable, Doctrine_Core::HYDRATE_ARRAY );
      $this->data = $this->getOrganizedResult($results, $this->dateFrom, $this->dateTo);
      
      // UI categories
      $cats = Doctrine::getTable( 'UiCategory')->findAll( Doctrine_Core::HYDRATE_ARRAY );
      $this->uicategories = $cats;
  }

  private function generateTableData( $results )
  {
  }

  private function getOrganizedResult( $results, $dateFrom, $dateTo )
  {
      if( !is_array( $results ) )
          return null;

      $data = array();

      // Create Date Range of Array
      $currentDate = date('Y-m-d', strtotime( $dateFrom ) );
      while( 1 )
      {
          $data[ $currentDate ] = array();

          $currentDate = date( 'Y-m-d', strtotime( '+1 day', strtotime( $currentDate ) ) );

          if( strtotime($currentDate) > strtotime( $dateTo ) )
              break;
      }

      foreach( $results as $record )
      {
          // Create Array when not exist to hold the count of each category occurreces
          $date = substr($record['created_at'],0,10);
          
          $history = array_pop( $record['ExportedItemHistory'] );
          $category_id = $history['value'];
          if( !isset( $data[ $date ][ $category_id ] ) )
          {
              $data[ $date ][ $category_id ] = 0;
          }

          // Incease the Count
          $data[ $date ][ $category_id ]++;
      }

      return empty( $data ) ? null : $data;
  }
}
