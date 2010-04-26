<?php

/**
 * LinkingVendorEventCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingVendorEventCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vendor_event_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'), 'add_empty' => true)),
      'event_id'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'vendor_event_category_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('VendorEventCategory'), 'column' => 'id')),
      'event_id'                 => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Event'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('linking_vendor_event_category_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingVendorEventCategory';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'vendor_event_category_id' => 'ForeignKey',
      'event_id'                 => 'ForeignKey',
    );
  }
}
