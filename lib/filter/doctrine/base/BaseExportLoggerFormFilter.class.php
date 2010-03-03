<?php

/**
 * ExportLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportLoggerFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
<<<<<<< HEAD:lib/filter/doctrine/base/BaseExportLoggerFormFilter.class.php
      'vendor_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'type'        => new sfWidgetFormChoice(array('choices' => array('' => '', 'movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'environment' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'total_time'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'vendor_id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'type'        => new sfValidatorChoice(array('required' => false, 'choices' => array('movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'environment' => new sfValidatorPass(array('required' => false)),
      'total_time'  => new sfValidatorPass(array('required' => false)),
      'created_at'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
=======
      'vendor_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'type'       => new sfWidgetFormChoice(array('choices' => array('' => '', 'movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'total_time' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'vendor_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'type'       => new sfValidatorChoice(array('required' => false, 'choices' => array('movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'total_time' => new sfValidatorPass(array('required' => false)),
      'created_at' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
>>>>>>> e6b61449c10006f3908b9410f7f38e2093bf28d2:lib/filter/doctrine/base/BaseExportLoggerFormFilter.class.php
    ));

    $this->widgetSchema->setNameFormat('export_logger_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExportLogger';
  }

  public function getFields()
  {
    return array(
<<<<<<< HEAD:lib/filter/doctrine/base/BaseExportLoggerFormFilter.class.php
      'id'          => 'Number',
      'vendor_id'   => 'ForeignKey',
      'type'        => 'Enum',
      'environment' => 'Text',
      'total_time'  => 'Text',
      'created_at'  => 'Date',
      'updated_at'  => 'Date',
=======
      'id'         => 'Number',
      'vendor_id'  => 'ForeignKey',
      'type'       => 'Enum',
      'total_time' => 'Text',
      'created_at' => 'Date',
      'updated_at' => 'Date',
>>>>>>> e6b61449c10006f3908b9410f7f38e2093bf28d2:lib/filter/doctrine/base/BaseExportLoggerFormFilter.class.php
    );
  }
}
