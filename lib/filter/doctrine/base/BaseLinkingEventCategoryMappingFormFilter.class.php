<?php

/**
 * LinkingEventCategoryMapping filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingEventCategoryMappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'event_category_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('EventCategory'), 'add_empty' => true)),
      'vendor_event_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'event_category_id'        => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('EventCategory'), 'column' => 'id')),
      'vendor_event_category_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('VendorEventCategory'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('linking_event_category_mapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingEventCategoryMapping';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'event_category_id'        => 'ForeignKey',
      'vendor_event_category_id' => 'ForeignKey',
    );
  }
}
