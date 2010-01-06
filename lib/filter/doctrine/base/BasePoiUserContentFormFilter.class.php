<?php

/**
 * PoiUserContent filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiUserContentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vender_ucid'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'comment_subject' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'comment_body'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_rating'     => new sfWidgetFormFilterInput(),
      'poi_user_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiUser'), 'add_empty' => true)),
      'poi_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => true)),
      'created_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'vender_ucid'     => new sfValidatorPass(array('required' => false)),
      'comment_subject' => new sfValidatorPass(array('required' => false)),
      'comment_body'    => new sfValidatorPass(array('required' => false)),
      'user_rating'     => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'poi_user_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('PoiUser'), 'column' => 'id')),
      'poi_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Poi'), 'column' => 'id')),
      'created_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('poi_user_content_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiUserContent';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'vender_ucid'     => 'Text',
      'comment_subject' => 'Text',
      'comment_body'    => 'Text',
      'user_rating'     => 'Number',
      'poi_user_id'     => 'ForeignKey',
      'poi_id'          => 'ForeignKey',
      'created_at'      => 'Date',
      'updated_at'      => 'Date',
    );
  }
}
