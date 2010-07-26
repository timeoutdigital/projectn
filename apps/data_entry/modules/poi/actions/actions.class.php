<?php

require_once dirname(__FILE__).'/../lib/poiGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/poiGeneratorHelper.class.php';

/**
 * poi actions.
 *
 * @package    sf_sandbox
 * @subpackage poi
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class poiActions extends autoPoiActions
{

  public function preExecute()
  {
     parent::preExecute();

     $filters = $this->getFilters() ;
     $user = $this->getUser();

     if ( !isset( $filters['vendor_id'] ) || !$user->checkIfVendorIdIsAllowed( $filters['vendor_id'] ) )
     {
          $this->setFilters( array( 'vendor_id' => $user->getCurrentVendorId() ) );
     }
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
        $this->redirect('@poi');
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        $request->checkCSRFProtection();

        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

        $poi = $this->getRoute()->getObject();

        $this->deleteRelations( $poi );

        if ($poi->delete())
        {
          $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
        }
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to delete this record' );        
    }

    $this->redirect('@poi');
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
        $this->redirect('@poi');
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        $ids = $request->getParameter('ids');

        $records = Doctrine_Query::create()
          ->from('poi')
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

    $this->redirect('@poi');
  }

  private function deleteRelations( Poi $poi )
  {
    //delete vendor category references
    $vendorCategoryIds = array();
    foreach( $poi[ 'VendorPoiCategory' ] as $vendorCategory )
    {
        $vendorCategoryIds[] =  $vendorCategory['id'];
    }
    $poi->unlink( 'VendorPoiCategory', $vendorCategoryIds );
    $poi->save();

    //delete media
    foreach( $poi[ 'PoiMedia' ] as $poiMedia )
    {
        $file = $poiMedia->getFileUploadStorePath() . '/' . $poiMedia[ 'url' ];

        if ( is_file($file) )
        {
            unlink($file);
        }
        $poiMedia->delete();
    }
  }

}
