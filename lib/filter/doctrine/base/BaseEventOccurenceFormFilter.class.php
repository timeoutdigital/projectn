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
      'vendor_event_occurence_id' => new sfWidgetFormFilterInput(),
      'booking_url'               => new sfWidgetFormFilterInput(),
      'start'                     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'end'                       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'utc_offset'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'event_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => true)),
      'poi_id'                    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'vendor_event_occurence_id' => new sfValidatorPass(array('required' => false)),
      'booking_url'               => new sfValidatorPass(array('required' => false)),
      'start'                     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'end'                       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'utc_offset'                => new sfValidatorPass(array('required' => false)),
      'event_id'                  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Event'), 'column' => 'id')),
      'poi_id'                    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Poi'), 'column' => 'id')),
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
      'id'                        => 'Number',
      'vendor_event_occurence_id' => 'Text',
      'booking_url'               => 'Text',
      'start'                     => 'Date',
      'end'                       => 'Date',
      'utc_offset'                => 'Text',
      'event_id'                  => 'ForeignKey',
      'poi_id'                    => 'ForeignKey',
    );
  }
}
