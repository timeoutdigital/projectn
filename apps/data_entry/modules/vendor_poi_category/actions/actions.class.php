<?php

require_once dirname(__FILE__).'/../lib/vendor_poi_categoryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/vendor_poi_categoryGeneratorHelper.class.php';

/**
 * vendor_poi_category actions.
 *
 * @package    sf_sandbox
 * @subpackage vendor_poi_category
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class vendor_poi_categoryActions extends autoVendor_poi_categoryActions
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
        $this->redirect('@vendor_poi_category');
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        $request->checkCSRFProtection();

        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

        //before trying to delete check if there are any pois attached to this category
        $vendorPoiCategory = $this->getRoute()->getObject();

        $pois = $vendorPoiCategory[ 'Poi' ]->toArray();

        $poiList = array();

        foreach ($pois as $poi)
        {
            $poiList [ $poi[ 'id' ] ] = $poi[ 'poi_name' ] ;
        }

        $errorData = array(
            'vendorPoiCategoryName' => $vendorPoiCategory[ 'name' ] ,
            'poiList'               => $poiList );

        if( count( $poiList ) > 0 )
        {
             //note : serialize doesn't work for this case so instead json is used
             $this->getUser()->setFlash('error_poi_category_delete', json_encode( $errorData ) );
        }
        else
        {
            if ($this->getRoute()->getObject()->delete())
            {
              $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
            }
        }

        $this->redirect('@vendor_poi_category');

    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to delete this record' );
        $this->redirect('@vendor_poi_category');
    }
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
        $this->redirect('@vendor_poi_category');
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        parent::executeBatchDelete( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
        $this->redirect('@vendor_poi_category');
    }
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('@vendor_poi_category');
  }

}
