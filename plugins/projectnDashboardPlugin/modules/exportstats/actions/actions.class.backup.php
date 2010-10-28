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
        $cities         = array();
        $models         = array('Poi','Event','Movie');
        $metrics        = array('insert','existing','failed','delete','received');
        $this->data     = array();
        
        $vendors = Doctrine::getTable("Vendor")->findAll();
        foreach( $vendors as $vendor ) $cities[] = $vendor['city'];

        foreach( $cities as $city )
        {
            $logs = Doctrine::getTable("LogImport")->getAllByCityName( $city );
            $this->data[ $city ] = array();
            
            foreach( $models as $model )
            {
                // Dont show stuff we don't have stats for
                if( empty( $logs ) || ( method_exists( $logs, 'count' ) && $logs->count() == 0 ) ) continue;
                
                $csv = '"Date';
                foreach( $metrics as $metric ) $csv .= "," . $model . ' ' . ucfirst( $metric );
                $csv .= '\n';

                foreach( $logs as $log )
                {
                    $csv .= $log->getDate();
                    foreach( $metrics as $metric )
                        $csv .= "," . $log->getCountFor( $model, array( $metric ) );
                    $csv .= '\n';
                }

                $csv .= '"';
                $this->data[ $city ][ $model ] = $csv;
            }
       }
  }
}
