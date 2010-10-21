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
    $importErrorId = $request->getGetParameter( 'import_error_id' );
    if( is_numeric( $importErrorId ) )
    {
        $logImportRecord = Doctrine::getTable( 'LogImportError' )->findOneById( $importErrorId );

        if( is_object( $logImportRecord ) && $logImportRecord instanceof LogImportError && isset( $logImportRecord[ 'serialized_object' ] ) )
        {
            $serializedObj = unserialize( $logImportRecord[ 'serialized_object' ] );

            if( is_numeric( $serializedObj['id'] ) )
            {
                $poi = Doctrine::getTable( 'Poi' )->findOneById( $serializedObj['id'] );
                $poi->merge( $serializedObj );
            }

            else $poi = $serializedObj;
        }
    }
    else $this->getUser()->setFlash('error', 'Import Error ID Not Numeric' );
    
    $this->form = $this->configuration->getForm( isset( $poi ) ? $poi : null );
    $this->poi = $this->form->getObject();

    $this->setTemplate('new');

    // Flag Error Resolved.
    // Apply Overrides.
    // Return User
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
