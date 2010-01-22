<?php

/**
 * EventCategoryMapping filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventCategoryMappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'map_from'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'map_to'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'vendor_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'map_from'  => new sfValidatorPass(array('required' => false)),
      'map_to'    => new sfValidatorPass(array('required' => false)),
      'vendor_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('event_category_mapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventCategoryMapping';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'map_from'  => 'Text',
      'map_to'    => 'Text',
      'vendor_id' => 'ForeignKey',
    );
  }
}
