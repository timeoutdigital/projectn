<?php

/**
 * RecordFieldOverride filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'field'          => new sfWidgetFormFilterInput(),
      'received_value' => new sfWidgetFormFilterInput(),
      'edited_value'   => new sfWidgetFormFilterInput(),
      'created_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'poi_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'event_list'     => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
      'movie_list'     => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Movie')),
    ));

    $this->setValidators(array(
      'field'          => new sfValidatorPass(array('required' => false)),
      'received_value' => new sfValidatorPass(array('required' => false)),
      'edited_value'   => new sfValidatorPass(array('required' => false)),
      'created_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'poi_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'event_list'     => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
      'movie_list'     => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Movie', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('record_field_override_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addPoiListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.OverridePoi OverridePoi')
          ->andWhereIn('OverridePoi.poi_id', $values);
  }

  public function addEventListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.OverrideEvent OverrideEvent')
          ->andWhereIn('OverrideEvent.event_id', $values);
  }

  public function addMovieListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.OverrideMovie OverrideMovie')
          ->andWhereIn('OverrideMovie.movie_id', $values);
  }

  public function getModelName()
  {
    return 'RecordFieldOverride';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'field'          => 'Text',
      'received_value' => 'Text',
      'edited_value'   => 'Text',
      'created_at'     => 'Date',
      'updated_at'     => 'Date',
      'poi_list'       => 'ManyKey',
      'event_list'     => 'ManyKey',
      'movie_list'     => 'ManyKey',
    );
  }
}
