<?php

/**
 * statistics actions.
 *
 * @package    sf_sandbox
 * @subpackage statistics
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class importstatsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
        $this->form = new MetricDimensionForm();
  }

  public function executeImporterrorinfo(sfWebRequest $request)
  {
      $this->logId  = $request->getGetParameter( 'id' );
      
      $q = Doctrine::getTable( 'LogImportError' )->createQuery('e')
        ->where('e.id=?', $this->logId )
        ->limit( 1 );

      $this->importError = $q->fetchArray();
  }

  public function executeImporterrors(sfWebRequest $request)
  {
      if( !is_numeric( $request->getPostParameter( 'date_month' ) ) )
      {
          $this->date = strtotime( 'today' );
      }
      else {
          $this->date = mktime( 0, 0, 0,
            $request->getPostParameter( 'date_month' ),
            $request->getPostParameter( 'date_day' ),
            $request->getPostParameter( 'date_year' ) );
      }

      $this->vendor = Doctrine::getTable( 'Vendor' )->findOneById( $request->getPostParameter( 'vendor_id' ) );
      $this->model  = $request->getPostParameter( 'model' );

      $this->errorList = Doctrine::getTable( 'LogImportError' )->getLogImportErrors( $this->vendor->id, $this->model, date( 'Y-m-d', $this->date ), date( 'Y-m-d', $this->date + 86400 ), Doctrine_Core::HYDRATE_ARRAY );
  }

  public function executeExporterrors(sfWebRequest $request)
  {
      if( !is_numeric( $request->getPostParameter( 'date_month' ) ) )
      {
          $this->date = strtotime( 'today' );
      }
      else {
          $this->date = mktime( 0, 0, 0,
            $request->getPostParameter( 'date_month' ),
            $request->getPostParameter( 'date_day' ),
            $request->getPostParameter( 'date_year' ) );
      }

      $this->vendor = Doctrine::getTable( 'Vendor' )->findOneById( $request->getPostParameter( 'vendor_id' ) );
      $this->model  = $request->getPostParameter( 'model' );

      $this->errorList = Doctrine::getTable( 'LogExportError' )->getLogExportErrors( $this->vendor->id, $this->model, date( 'Y-m-d', $this->date ), date( 'Y-m-d', $this->date + 86400 ), Doctrine_Core::HYDRATE_ARRAY );
  }

  public function executeGraph(sfWebRequest $request)
  {
      if( !is_numeric( $request->getPostParameter( 'date_from_month' ) ) )
      {
          $this->date_from = strtotime( '-2 weeks' );
      }
      else {
          $this->date_from = mktime( 0, 0, 0,
            $request->getPostParameter( 'date_from_month' ),
            $request->getPostParameter( 'date_from_day' ),
            $request->getPostParameter( 'date_from_year' ) );
      }

      if( !is_numeric( $request->getPostParameter( 'date_to_month' ) ) )
      {
          $this->date_to = time();
      }
      else {
          $this->date_to = mktime( 0, 0, 0,
            $request->getPostParameter( 'date_to_month' ),
            $request->getPostParameter( 'date_to_day' ),
            $request->getPostParameter( 'date_to_year' ) );
      }

      $this->form = new DateRangeSelectionForm();
      $this->form->setDefault( 'date', array( 'from' =>  $this->date_from, 'to' => $this->date_to ) );

      $this->vendor = Doctrine::getTable( 'Vendor' )->findOneById( $request->getPostParameter( 'vendor_id' ) );
      $this->model  = $request->getPostParameter( 'model' );

      $q = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $this->vendor->id, date( 'Y-m-d', $this->date_from ), date( 'Y-m-d', $this->date_to + ( 60 * 60 * 24 ) ), Doctrine_Core::HYDRATE_ARRAY );
      $this->stats = $this->extractStats( $q );
  }

  public function executePane(sfWebRequest $request)
  {
      if( !is_numeric( $request->getPostParameter( 'date_month' ) ) )
      {
          $this->date = strtotime( 'today' );
      }
      else {
          $this->date = mktime( 0, 0, 0,
            $request->getPostParameter( 'date_month' ),
            $request->getPostParameter( 'date_day' ),
            $request->getPostParameter( 'date_year' ) );
      }

      $this->form = new DateSelectionForm();
      $this->form->setDefault( 'date', $this->date );

      $this->vendor = Doctrine::getTable( 'Vendor' )->findOneById( $request->getPostParameter( 'vendor_id' ) );
      $this->model  = $request->getPostParameter( 'model' );

      $q = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecords( $this->vendor->id, date( 'Y-m-d', strtotime( '-1 day', $this->date ) ), date( 'Y-m-d', strtotime( '+1 day', $this->date ) ), Doctrine_Core::HYDRATE_ARRAY );
      $this->statsPanel = $this->extractStats( $q );

      $q = Doctrine::getTable( $this->model )->createQuery('l')
        ->select('count(*)')
        ->where( 'l.vendor_id=?', $this->vendor->id );

      $this->dbtotal = $q->fetchArray();
      $this->dbtotal = $this->dbtotal[0]['count'];

      $this->exportStats = Doctrine::getTable( 'LogExport' )->getLogExportWithCountRecordsForDate( $this->vendor->id, $this->model, $this->date, Doctrine_Core::HYDRATE_ARRAY );
  }

    public function extractStats( array $array )
    {
        $metrics = array( 'insert' => 0, 'failed' => 0, 'updated' => 0, 'existing' => 0 );
        $dates = array();

        foreach( $array as $logImport )
        {
            $date = date( 'Y-m-d', strtotime( $logImport['created_at'] ) );

            if( !array_key_exists( $date, $dates ) )
                $dates[ $date ] = array( 'Poi' => $metrics, 'Event' => $metrics, 'Movie' => $metrics, 'EventOccurrence' => $metrics );

            foreach( $logImport['LogImportCount'] as $logImportCount )
                $dates[ $date ][ $logImportCount['model'] ][ $logImportCount['operation'] ] += $logImportCount['count'];
        }

        return $dates;
    }
}