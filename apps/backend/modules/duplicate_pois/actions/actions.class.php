<?php

require_once dirname(__FILE__).'/../lib/duplicate_poisGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/duplicate_poisGeneratorHelper.class.php';

/**
 * duplicate_pois actions.
 *
 * @package    sf_sandbox
 * @subpackage duplicate_pois
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class duplicate_poisActions extends autoDuplicate_poisActions
{
    public function executeProcessMasterSlave( sfWebRequest $request )
    {
        $poiIDs = $request->getParameter( 'poi_id' );
        $masterPoiID = $request->getParameter( 'masterid' );

        if( !is_array($poiIDs) || empty($poiIDs))
        {
            $this->getUser()->setFlash('error', 'No Pois found!');
            $this->forward('duplicate_pois', 'index');
        }

        if(!is_array($masterPoiID) || count($masterPoiID) != 1 )
        {
            $this->getUser()->setFlash('error', 'Invalid number of master poi selected or not selected at all');
            $this->forward('duplicate_pois', 'index');
        }

        // Get the master POI and process
        $master_poi = Doctrine::getTable( 'Poi' )->find( $masterPoiID[0] );
        if( $master_poi === false )
        {
            $this->getUser()->setFlash('error', 'invalid master poi ID, no poi found');
            $this->forward('duplicate_pois', 'index');
        }
        
        foreach( $poiIDs as $id)
        {
            if( $id == $masterPoiID[0] )
                continue;

            $poi = Doctrine::getTable( 'Poi' )->find( $id );
            if( $poi === false )
            {
                $this->getUser()->setFlash('error', 'One or more invalid poi id, unable to get poi from db');
                continue;
            }

            try{
                // Add as duplicate
                $poi->setMasterPoi( $master_poi );
                $poi->save();
            } catch ( Exception $e ){
                $this->getUser()->setFlash('error', $e->getMessage() );
                $this->redirect('duplicate_pois/index');
                exit();
            }
        }
        
        $this->getUser()->setFlash('notice', 'Duplicate Pois updated');
        $this->redirect('duplicate_pois/index');
    }
}
