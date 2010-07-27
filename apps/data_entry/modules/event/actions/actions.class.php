<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    sf_sandbox
 * @subpackage event
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{

  private $user;

  public function preExecute()
  {
     parent::preExecute();
     

     $filters = $this->getFilters() ;
     $this->user = $this->getUser();

     if ( !isset( $filters['vendor_id'] ) || !$this->user->checkIfVendorIdIsAllowed( $filters['vendor_id'] ) )
     {
          $this->setFilters( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
     }
  }

  public function executeAjax($request)
  {
    $this->getResponse()->setContentType('application/json');
    
    $q = Doctrine_Query::create()
                ->select( 'id, poi_name name' )
                ->from('Poi p')
                ->where( 'vendor_id = ?', $this->user->getCurrentVendorId() )
                ->andWhere( 'poi_name LIKE ?', '%' . $request->getParameter('q') . '%' );

    $result = $q->fetchArray();
    
    $pois = array();    
    foreach ( $result as $poi )
    {
        $pois[ $poi['id'] ] = $poi['name'];
    }

    return $this->renderText(json_encode($pois));
  }


  public function executeEdit(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        parent::executeEdit( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change this record' );
        $this->redirect('@event');
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        $request->checkCSRFProtection();

        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

        $event = $this->getRoute()->getObject();

        $this->deleteRelations( $event );

        if ($event->delete())
        {
          $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
        }
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to delete this record' );        
    }
    
    $this->redirect('@event');
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        parent::executeBatch( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
        $this->redirect('@event');
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        $ids = $request->getParameter('ids');

        $records = Doctrine_Query::create()
          ->from('event')
          ->whereIn('id', $ids)
          ->execute();

        foreach ($records as $record)
        {
          $this->deleteRelations( $record );
          $record->delete();
        }

        $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
    }

    $this->redirect('@event');
  }

  private function deleteRelations( Event $event )
  {
    //delete vendor category references
    $vendorCategoryIds = array();
    foreach( $event[ 'VendorEventCategory' ] as $vendorCategory )
    {
        $vendorCategoryIds[] =  $vendorCategory['id'];
    }
    $event->unlink( 'VendorEventCategory', $vendorCategoryIds );
    $event->save();

    //delete occurrences
    $event[ 'EventOccurrence' ]->delete();

    //delete meta
    $event[ 'EventMeta' ]->delete();

    //delete media
    foreach( $event[ 'EventMedia' ] as $eventMedia )
    {
        $file = $eventMedia->getFileUploadStorePath() . '/' . $eventMedia[ 'url' ];

        if ( is_file($file) )
        {
            unlink($file);
        }
        $eventMedia->delete();
    }
  }

}
