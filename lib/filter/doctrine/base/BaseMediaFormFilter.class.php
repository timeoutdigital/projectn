<?php

/**
 * Media filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMediaFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'ident'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'url'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'mime_type'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'file_last_modified' => new sfWidgetFormFilterInput(),
      'etag'               => new sfWidgetFormFilterInput(),
      'content_length'     => new sfWidgetFormFilterInput(),
      'created_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'ident'              => new sfValidatorPass(array('required' => false)),
      'url'                => new sfValidatorPass(array('required' => false)),
      'mime_type'          => new sfValidatorPass(array('required' => false)),
      'file_last_modified' => new sfValidatorPass(array('required' => false)),
      'etag'               => new sfValidatorPass(array('required' => false)),
      'content_length'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('media_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Media';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'ident'              => 'Text',
      'url'                => 'Text',
      'mime_type'          => 'Text',
      'file_last_modified' => 'Text',
      'etag'               => 'Text',
      'content_length'     => 'Number',
      'created_at'         => 'Date',
      'updated_at'         => 'Date',
    );
  }
}
