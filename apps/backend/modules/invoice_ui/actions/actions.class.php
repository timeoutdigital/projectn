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
      // Remove unKnown from the List
      unset($vendorList[17]);
        
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

      // Get from Database
      $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( $this->dateFrom, $this->dateTo, $vendor_ID, $this->model );
      $this->data = $this->getOrganizedResult($results, $this->dateFrom, $this->dateTo);
      
      // UI categories
      $cats = Doctrine::getTable( 'UiCategory')->findAll( Doctrine_Core::HYDRATE_ARRAY );
      $this->uicategories = $cats;
  }

  public function executeGenerateMonthlyReport(sfWebRequest $request)
  {
      // Get params
      $this->model  = $request->getParameter( 'model' );
      $month = $request->getParameter( 'month' );
      $year = $request->getParameter( 'year' );
      $this->invoiceable = ($request->getParameter( 'invoiceable' ) == 'true') ? true:false;
      
      // UI categories
      $cats = Doctrine::getTable( 'UiCategory')->findAll( Doctrine_Core::HYDRATE_ARRAY );
      $this->uicategories = $cats;

      // Generate Date range based on Month / Year range
      $this->dateRange = $this->getFromToDateBy( $month, $year );

      // Query Database for Each vendor
      $this->vendorList = Doctrine::getTable('Vendor')->findAll( 'KeyValue' );
      unset( $this->vendorList[17] ); // Remove Unknown from the List
      $this->vendorResults = array();
      foreach( $this->vendorList as $key => $city )
      {
          $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn(  $this->dateRange['from'], $this->dateRange['to'], $key, $this->model );
          $this->vendorResults[ $key ] = $this->getVendorSpecificReport($results);
          unset( $results );
      }
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
          
          $category_id = $record['value'];
          if( !isset( $data[ $date ][ $category_id ] ) )
          {
              $data[ $date ][ $category_id ] = 0;
          }

          // Incease the Count
          $data[ $date ][ $category_id ]++;
      }

      return empty( $data ) ? null : $data;
  }

  private function getVendorSpecificReport( $results )
  {
      $data = array();
//      foreach( $results as $record )
//      {
//          $history = array_pop( $record['ExportedItemHistory'] );
//          $category_id = $history['value'];
//          if( !isset( $data[$category_id] ) )
//              $data[$category_id] = 0;
//
//          $data[$category_id]++;
//      }
      foreach( $results as $record )
      {
          $category_id = $record['value'];
          if( !isset( $data[$category_id] ) )
              $data[$category_id] = 0;

          $data[$category_id]++;
      }

      return $data;
  }

  private function getFromToDateBy( $formMonth, $year)
  {
      $date = array();
      if( $formMonth == 12 )
      {
          $date['to'] = date('Y/m/16', strtotime( "16 January $year") );
          $year--;
          $date['from'] = date('Y/m/17', strtotime( "17 December $year") );
      }
      else
      {
          $date['from'] = date( 'Y/m/17', strtotime( "$year/$formMonth/17" ));
          $formMonth++;
          $date['to'] = date( 'Y/m/16', strtotime( "$year/$formMonth/16" ));
      }

      return $date;
  }
}
