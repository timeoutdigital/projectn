<?php

/**
 * statistics actions.
 *
 * @package    sf_sandbox
 * @subpackage statistics
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class statisticsActions extends sfActions
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

  public function executePane(sfWebRequest $request)
  {
      $this->date_from = mktime( 0, 0, 0,
        $request->getPostParameter( 'date_from_month' ),
        $request->getPostParameter( 'date_from_day' ),
        $request->getPostParameter( 'date_from_year' ) );

      $this->date_to = mktime( 0, 0, 0,
        $request->getPostParameter( 'date_to_month' ),
        $request->getPostParameter( 'date_to_day' ),
        $request->getPostParameter( 'date_to_year' ) );

      $this->vendor = Doctrine::getTable( 'Vendor' )->findOneById( $request->getPostParameter( 'vendor_id' ) );
      $this->model  = $request->getPostParameter( 'model' );

//      echo '<pre style="padding:20px; background-color: white;">';
//      var_dump( date( 'Y-m-d', $this->date_from ) );
//      var_dump( date( 'Y-m-d', $this->date_to ) );
//      print_r( $this->vendor->toArray() );
//      var_dump( $this->model );
//      echo '</pre>';

      $q = Doctrine::getTable( 'LogImport' )->createQuery('l')
        ->leftJoin( 'l.LogImportCount lc ON l.id = lc.log_import_id AND lc.model=?', $this->model )
        ->where( 'l.vendor_id=?', $this->vendor->id )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( date( 'Y-m-d', $this->date_from ), date( 'Y-m-d', $this->date_to + ( 60 * 60 * 24 ) ) ) );

      $this->stats = $this->extractStats( $q->fetchArray() );

      $q = Doctrine::getTable( 'LogImport' )->createQuery('l')
        ->leftJoin( 'l.LogImportCount lc ON l.id = lc.log_import_id' )
        ->where( 'l.vendor_id=?', $this->vendor->id )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( date( 'Y-m-d', strtotime( '-1 day' ) ), date( 'Y-m-d', strtotime( '+1 day' ) ) ) );

      $this->statsPanel = $this->extractStats( $q->fetchArray() );

      $q = Doctrine::getTable( $this->model )->createQuery('l')
        ->select('count(*)')
        ->where( 'l.vendor_id=?', $this->vendor->id );

      $this->dbtotal = $q->fetchArray();
      $this->dbtotal = $this->dbtotal[0]['count'];

      $q = Doctrine::getTable( 'LogExport' )->createQuery('l')
        ->leftJoin('l.LogExportCount c ON l.id = c.log_export_id')
        ->where('c.model=?', $this->model )
        ->addWhere( 'l.created_at = DATE(NOW())' );

      $this->exportStats = $q->fetchArray();

      

//      echo '<pre style="padding:20px; background-color: white;">';
//      print_r( $this->dbtotal );
//      echo '</pre>';
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

            if( !isset( $logImport['LogImportCount'] ) ) die('noononononon' );
            foreach( $logImport['LogImportCount'] as $logImportCount )
                $dates[ $date ][ $logImportCount['model'] ][ $logImportCount['operation'] ] += $logImportCount['count'];
        }

        return $dates;
    }
}
