<?php

/**
 * ImportLoggerSuccess filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportLoggerSuccessFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'type'             => new sfWidgetFormChoice(array('choices' => array('' => '', 'insert' => 'insert', 'update' => 'update', 'delete' => 'delete'))),
      'log'              => new sfWidgetFormFilterInput(),
      'import_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => true)),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'poi_list'         => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'event_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
      'movie_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Movie')),
    ));

    $this->setValidators(array(
      'type'             => new sfValidatorChoice(array('required' => false, 'choices' => array('insert' => 'insert', 'update' => 'update', 'delete' => 'delete'))),
      'log'              => new sfValidatorPass(array('required' => false)),
      'import_logger_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLogger'), 'column' => 'id')),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'poi_list'         => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'event_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
      'movie_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Movie', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('import_logger_success_filters[%s]');

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

    $query->leftJoin('r.LinkingImportLoggerSuccessPoi LinkingImportLoggerSuccessPoi')
          ->andWhereIn('LinkingImportLoggerSuccessPoi.poi_id', $values);
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

    $query->leftJoin('r.LinkingImportLoggerSuccessEvent LinkingImportLoggerSuccessEvent')
          ->andWhereIn('LinkingImportLoggerSuccessEvent.event_id', $values);
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

    $query->leftJoin('r.LinkingImportLoggerSuccessMovie LinkingImportLoggerSuccessMovie')
          ->andWhereIn('LinkingImportLoggerSuccessMovie.movie_id', $values);
  }

  public function getModelName()
  {
    return 'ImportLoggerSuccess';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'type'             => 'Enum',
      'log'              => 'Text',
      'import_logger_id' => 'ForeignKey',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
      'poi_list'         => 'ManyKey',
      'event_list'       => 'ManyKey',
      'movie_list'       => 'ManyKey',
    );
  }
}
