<?php

/**
 * VendorCategoryMapping filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorCategoryMappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'map_from_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorPoiCategory'), 'add_empty' => true)),
      'map_to_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'map_from_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('VendorPoiCategory'), 'column' => 'id')),
      'map_to_id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('PoiCategory'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('vendor_category_mapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'VendorCategoryMapping';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'map_from_id' => 'ForeignKey',
      'map_to_id'   => 'ForeignKey',
    );
  }
}
