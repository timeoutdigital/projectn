<?php

/**
 * EventMedia form base class.
 *
 * @method EventMedia getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventMediaForm extends MediaForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['event_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => false));
    $this->validatorSchema['event_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Event')));

    $this->widgetSchema->setNameFormat('event_media[%s]');
  }

  public function getModelName()
  {
    return 'EventMedia';
  }

}
