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
        $this->city  = "singapore";
        $this->model = "Poi";

        //$log = Doctrine::getTable("LogImport")->getLatestOneByCityName( "tyumen" );
        $logs = Doctrine::getTable("LogImport")->getAllByCityName( $this->city );

        $this->stats = '"';
        $this->stats .= 'Date,'.$this->model.' Inserts,'.$this->model.' Failed\n';

        foreach( $logs as $log )
        {
            $this->stats .= $log->getDate() . ",";
            $this->stats .= $log->getCountFor( $this->model, array('insert') ) . ",";
            $this->stats .= $log->getCountFor( $this->model, array('failed') );
            $this->stats .= '\n';
        }

        $this->stats .= '"';
  }
}
