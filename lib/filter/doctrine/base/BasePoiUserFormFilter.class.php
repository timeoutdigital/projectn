<?php

/**
 * PoiUser filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiUserFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vender_id'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_name'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_reputation'    => new sfWidgetFormFilterInput(),
      'user_infomation'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'comments_relevance' => new sfWidgetFormFilterInput(),
      'specialty'          => new sfWidgetFormFilterInput(),
      'created_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'vender_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_name'          => new sfValidatorPass(array('required' => false)),
      'user_reputation'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_infomation'    => new sfValidatorPass(array('required' => false)),
      'comments_relevance' => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'specialty'          => new sfValidatorPass(array('required' => false)),
      'created_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('poi_user_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiUser';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'vender_id'          => 'Number',
      'user_name'          => 'Text',
      'user_reputation'    => 'Number',
      'user_infomation'    => 'Text',
      'comments_relevance' => 'Number',
      'specialty'          => 'Text',
      'created_at'         => 'Date',
      'updated_at'         => 'Date',
    );
  }
}
