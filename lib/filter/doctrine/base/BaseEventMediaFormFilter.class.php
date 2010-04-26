<?php

/**
 * EventMedia filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventMediaFormFilter extends MediaFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['event_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => true));
    $this->validatorSchema['event_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Event'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('event_media_filters[%s]');
  }

  public function getModelName()
  {
    return 'EventMedia';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'event_id' => 'ForeignKey',
    ));
  }
}
