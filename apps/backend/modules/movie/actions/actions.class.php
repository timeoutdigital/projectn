<?php

require_once dirname(__FILE__).'/../lib/movieGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/movieGeneratorHelper.class.php';

/**
 * movie actions.
 *
 * @package    sf_sandbox
 * @subpackage movie
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class movieActions extends autoMovieActions
{
  public function executeResolve(sfWebRequest $request)
  {
    $recordInfo = LogImportErrorHelper::getMergedObject( $this, $request );

    $this->form = $this->configuration->getForm( $recordInfo[ 'record' ] );
    $this->movie = $this->form->getObject();

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
    $this->movie = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->movie);

    $widgetSchema = $this->form->getWidgetSchema();
    /* set original values as help text */
    $widgetSchema->setHelps( $this->movie->toArray() );
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->movie = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->movie);

    $widgetSchema = $this->form->getWidgetSchema();
    /* set original values as help text */
    $widgetSchema->setHelps( $this->movie->toArray() );

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeCreate(sfWebRequest $request)
  {
    $vendorId = $request->getPostParameter( 'movie[vendor_id]' );

    if ( !is_numeric( $vendorId ) )
    {
        $this->getUser()->setFlash('error', 'Invalid Vendor Id');
        $this->redirect('@movie_new');
    }

    $vendor = Doctrine::getTable( 'Vendor' )->findOneById( $vendorId );

    if ( $vendor === false)
    {
        $this->getUser()->setFlash('error', 'Vendor does not exist');
        $this->redirect('@movie_new');
    }

    $this->form = $this->configuration->getForm( array(), array( 'vendor_id' => $vendor['id'] ) );
    $this->movie = $this->form->getObject();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  /*** symfony generated start taken from cache/backend/dev/modules/autoMovie/actions/actions.class.php ***/
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $notice = $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.';

      try {
        $movie = $form->save();
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

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $movie)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $notice.' You can add another one below.');

        $this->redirect('@movie_new');
      }
      else
      {
        $this->getUser()->setFlash('notice', $notice);

        /*** custom code start ***/
        $this->redirect( '@movie' );
        /*** custom code end ***/

        $this->redirect(array('sf_route' => 'movie_edit', 'sf_subject' => $movie));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }
  /*** symfony generated end ***/
}
