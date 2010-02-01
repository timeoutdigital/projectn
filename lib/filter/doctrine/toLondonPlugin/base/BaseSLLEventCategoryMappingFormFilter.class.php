<?php

/**
 * SLLEventCategoryMapping filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventCategoryMappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'annotation_behavior' => new sfWidgetFormFilterInput(),
      'annotation'          => new sfWidgetFormFilterInput(),
      'master_category_id'  => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'annotation_behavior' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'annotation'          => new sfValidatorPass(array('required' => false)),
      'master_category_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sll_event_category_mapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEventCategoryMapping';
  }

  public function getFields()
  {
    return array(
      'category_id'         => 'Number',
      'event_id'            => 'Number',
      'annotation_behavior' => 'Number',
      'annotation'          => 'Text',
      'master_category_id'  => 'Number',
    );
  }
}
