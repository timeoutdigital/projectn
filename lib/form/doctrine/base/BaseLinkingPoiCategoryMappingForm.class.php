<?php

/**
 * LinkingPoiCategoryMapping form base class.
 *
 * @method LinkingPoiCategoryMapping getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingPoiCategoryMappingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'poi_category_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => false)),
      'vendor_poi_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorPoiCategory'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'poi_category_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'))),
      'vendor_poi_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('VendorPoiCategory'))),
    ));

    $this->widgetSchema->setNameFormat('linking_poi_category_mapping[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingPoiCategoryMapping';
  }

}
