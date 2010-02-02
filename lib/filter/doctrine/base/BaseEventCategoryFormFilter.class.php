<?php

/**
 * EventCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'events_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
    ));

    $this->setValidators(array(
      'name'        => new sfValidatorPass(array('required' => false)),
      'events_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_category_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addEventsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingEventCategory LinkingEventCategory')
          ->andWhereIn('LinkingEventCategory.event_id', $values);
  }

  public function getModelName()
  {
    return 'EventCategory';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'name'        => 'Text',
      'events_list' => 'ManyKey',
    );
  }
}