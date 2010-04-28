<?php

/**
 * LinkingPoiCategoryMapping filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingPoiCategoryMappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'poi_category_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => true)),
      'vendor_poi_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorPoiCategory'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'poi_category_id'        => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('PoiCategory'), 'column' => 'id')),
      'vendor_poi_category_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('VendorPoiCategory'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('linking_poi_category_mapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingPoiCategoryMapping';
  }

  public function getFields()
  {
    return array(
      'id'                     => 'Number',
      'poi_category_id'        => 'ForeignKey',
      'vendor_poi_category_id' => 'ForeignKey',
    );
  }
}
