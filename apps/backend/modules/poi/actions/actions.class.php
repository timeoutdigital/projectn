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
  public function executeResolve(sfWebRequest $request)
  {
    $recordInfo = LogImportErrorHelper::getMergedObject( $this, $request );

    $this->form = $this->configuration->getForm( $recordInfo[ 'record' ] );
    $this->poi = $this->form->getObject();

    $widgetSchema = $this->form->getWidgetSchema();
    /* set original values as help text */
    $widgetSchema->setHelps( $recordInfo[ 'previousValues' ] );
    /* set error id in form */
    $importErrorId = $request->getParameter( 'import_error_id' );
    $widgetSchema[ 'import_error_id' ]->setDefault( $importErrorId );

    isset( $recordInfo[ 'record' ][ 'id' ] ) ?  $this->setTemplate('edit') : $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->poi = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->poi);

    $widgetSchema = $this->form->getWidgetSchema();
    /* set original values as help text */
    $widgetSchema->setHelps( $this->poi->toArray() );
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->poi = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->poi);

    $widgetSchema = $this->form->getWidgetSchema();
    /* set original values as help text */
    $widgetSchema->setHelps( $this->poi->toArray() );

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeCreate(sfWebRequest $request)
  {
    $vendorId = $request->getPostParameter( 'poi[vendor_id]' );
    
    if ( !is_numeric( $vendorId ) )
    {
        $this->getUser()->setFlash('error', 'Invalid Vendor Id');
        $this->redirect('@poi_new');
    }
    
    $vendor = Doctrine::getTable( 'Vendor' )->findOneById( $vendorId );

    if ( $vendor === false)
    {
        $this->getUser()->setFlash('error', 'Vendor does not exist');
        $this->redirect('@poi_new');
    }

    $this->form = $this->configuration->getForm( array(), array( 'vendor_id' => $vendor['id'] ) );
    $this->poi = $this->form->getObject();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeAjaxGetRelatedPoi(sfWebRequest $request)
  {
      $vendor = $request->getParameter( 'vendor' );
      $keyword = $request->getParameter( 'q' );

      $returnMsg = array();
      
      if( !is_numeric($vendor) || $vendor <= 0 || stringTransform::mb_trim( $keyword ) == '' )
      {
          $returnMsg[0] = 'Invalid Vendor or Keyword';
      }

      // Do search and Return Results
      $searchResults = Doctrine::getTable( 'Poi' )->searchAllNonDuplicateAndNonMasterPoisBy( $vendor, $keyword, Doctrine_Core::HYDRATE_ARRAY );
      
      if( !is_array($searchResults) || count( $searchResults ) <= 0 )
      {
          $returnMsg[0] = 'No poi found';
      }
      else
      {
          foreach( $searchResults as $poiArray )
          {
              $returnMsg[ $poiArray['id'] ] = $poiArray['poi_name'];
          }
      }

      return $this->renderText(json_encode( $returnMsg ));
  }

  public function executeAjaxPoiList( sfWebRequest $request )
  {
      $result['status'] = 'success'; // success otherwise stated
      $result['pois'] = array();
      // get Pois
      $poiID = $request->getParameter( 'current_poi_id' );
      switch ($request->getParameter( 'get_type' ) )
      {
          case 'master':
              
              $masterPOI = Doctrine::getTable( 'Poi' )->getMasterOf( $poiID, Doctrine_Core::HYDRATE_ARRAY );
              if( !is_array( $masterPOI ) || count( $masterPOI ) <= 0 )
              {
                  $result['status'] = 'error';
                  $result['message'] = 'No Master poi found for POI ID: ' . $poiID;
                  break;
              }

              $result['pois'][] = $this->ajaxPoiToJsonArray( $masterPOI );
              break;

          case 'duplicate':
              
              $pois = Doctrine::getTable( 'Poi' )->getDuplicatesOf( $poiID, Doctrine_Core::HYDRATE_ARRAY );
              if( !is_array( $pois ) || count( $pois ) <= 0 )
              {
                  $result['status'] = 'error';
                  $result['message'] = 'No Duplicate pois found';
                  break;
              }

              foreach( $pois as $poi )
                  $result['pois'][] = $this->ajaxPoiToJsonArray( $poi );
              
              break;

          default:
              $result['status'] = 'error';
              $result['message'] = 'Invalid type requested!';
              break;
      }
      return $this->renderText( json_encode( $result ) );
  }

  private function ajaxPoiToJsonArray( &$poi )
  {
      return array(
              'name' => $poi['poi_name'],
              'id' => $poi['id']
              );
  }
  

  /*** symfony generated start taken from cache/backend/dev/modules/autoPoi/actions/actions.class.php ***/
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

    if ($form->isValid())
    {
      $notice = $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.';

      try {

        $poi = $form->getObject();

        // Add GEO Source.
        if( isset( $poi[ 'id' ] ) && is_numeric( $poi[ 'id' ] ) )
        {
            // Get Old Poi.
            $oldPoi = Doctrine::getTable( 'Poi' )->find( $poi[ 'id' ], Doctrine::HYDRATE_ARRAY );

            // If latitude or longitude has changed.
            if( isset( $oldPoi[ 'latitude' ] ) && isset( $oldPoi[ 'longitude' ] ) &&
                ( $oldPoi[ 'latitude' ] != $poi[ 'latitude' ] || $oldPoi[ 'longitude' ] != $poi[ 'longitude' ] ) )
            {
                $poi->addMeta( 'Geo_Source', 'Producer', sprintf( 'Changed %s:%s - %s:%s', $oldPoi[ 'latitude' ], $oldPoi[ 'longitude' ], $poi[ 'latitude' ], $poi[ 'longitude' ] ));
            }
        }

        $poi = $form->save();
        
      } catch (Doctrine_Validator_Exception $e) {

        $errorStack = $form->getObject()->getErrorStack();

        $message = get_class($form->getObject()) . ' has ' . count($errorStack) . " field" . (count($errorStack) > 1 ?  's' : null) . " with validation errors: ";
        foreach ($errorStack as $field => $errors) {
            $message .= "$field (" . implode(", ", $errors) . "), ";
        }
        $message = trim($message, ', ');

        $this->getUser()->setFlash('error', $message);
        return sfView::SUCCESS;
      }

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $poi)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $notice.' You can add another one below.');

        $this->redirect('@poi_new');
      }
      else
      {
        $this->getUser()->setFlash('notice', $notice);

        /*** custom code start ***/
        $this->redirect( '@poi' );
        /*** custom code end ***/

        $this->redirect(array('sf_route' => 'poi_edit', 'sf_subject' => $poi));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }
  /*** symfony generated end ***/

  
}
