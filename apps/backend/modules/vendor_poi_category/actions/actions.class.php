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

  /**
   * @todo keep this file in mind when upgrading, its basically a full overload
   * of the standard function to fix a bug (http://trac.symfony-project.org/ticket/5330)
   */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $notice = $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.';

      try {
        $vendor_poi_category = $form->save();
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

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $vendor_poi_category)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $notice.' You can add another one below.');

        $this->redirect('@vendor_poi_category_new');
      }


      // start custom change
      elseif ($request->hasParameter('_save_and_list'))
      {
          $this->getUser()->setFlash('notice', $notice);
          $this->redirect('@vendor_poi_category');
      }
      //end custom change


      else
      {
        $this->getUser()->setFlash('notice', $notice);

        $this->redirect(array('sf_route' => 'vendor_poi_category_edit', 'sf_subject' => $vendor_poi_category));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }

}
