<?php

/**
 * EventOccurence filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventOccurenceFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vender_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'booking_url' => new sfWidgetFormFilterInput(),
      'start_date'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'end_date'    => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'start_time'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'end_time'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'utc_offset'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'event_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => true)),
      'poi_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'vender_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'booking_url' => new sfValidatorPass(array('required' => false)),
      'start_date'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'end_date'    => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'start_time'  => new sfValidatorPass(array('required' => false)),
      'end_time'    => new sfValidatorPass(array('required' => false)),
      'utc_offset'  => new sfValidatorPass(array('required' => false)),
      'event_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Event'), 'column' => 'id')),
      'poi_id'      => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Poi'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('event_occurence_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventOccurence';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'vender_id'   => 'Number',
      'booking_url' => 'Text',
      'start_date'  => 'Date',
      'end_date'    => 'Date',
      'start_time'  => 'Text',
      'end_time'    => 'Text',
      'utc_offset'  => 'Text',
      'event_id'    => 'ForeignKey',
      'poi_id'      => 'ForeignKey',
    );
  }
}
