<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class errorstatsActions extends sfActions
{
    /**
    * Executes index action
    *
    * @param sfRequest $request A request object
    */
    public function executeIndex(sfWebRequest $request)
    {
        
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

      $this->model  = $request->getPostParameter( 'model' );
      if( !is_string( $this->model ) || trim($this->model) == '')
      {
          $this->model = 'Poi';
      }

      $this->form = new DateRangeSelectionForm();
      $this->form->setDefault( 'date', array( 'from' =>  $this->date_from, 'to' => $this->date_to ) );
      $this->form->setDefault( 'model', $this->model);

      $q = Doctrine::getTable( 'LogImport' )->getLogImportWithCountRecordsByModelAndStatus( ucwords( $this->model ),'failed', date( 'Y-m-d', $this->date_from ), date( 'Y-m-d', $this->date_to + ( 60 * 60 * 24 ) ), Doctrine_Core::HYDRATE_ARRAY );
      $this->stats = $this->extractFailedStats( $q );

      // Get all vendors and Attach them to VIEW
      $this->vendors = Doctrine::getTable( 'Vendor' )->findAll( 'KeyValue' );
      
    }

    public function extractFailedStats( array $array )
    {
        $vendorSpecifictStats = array();

        foreach( $array as $logImport )
        {

            $date = date( 'Y-m-d', strtotime( $logImport['created_at'] ) );

            if( !array_key_exists( $date, $vendorSpecifictStats ) )
            {
                $vendorSpecifictStats[ $date ] = array();
            }

            $vendorID = $logImport['vendor_id'];

            if( !array_key_exists( $vendorID , $vendorSpecifictStats[ $date ] ) )
            {
                $vendorSpecifictStats[ $date ][ $vendorID ] = 0;
            }

            foreach( $logImport['LogImportCount'] as $logImportCount )
            {
                if( $logImportCount['operation'] == 'failed' ) // only take failed
                {
                    $vendorSpecifictStats[ $date ][ $vendorID ] += $logImportCount['count'];
                }
            }
        }

        return $vendorSpecifictStats;
    }
}