<?php

/**
 * LinkingVendorPoiCategory form base class.
 *
 * @method LinkingVendorPoiCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingVendorPoiCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'vendor_poi_category_id' => new sfWidgetFormInputText(),
      'poi_id'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_poi_category_id' => new sfValidatorInteger(),
      'poi_id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
    ));

    $this->widgetSchema->setNameFormat('linking_vendor_poi_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingVendorPoiCategory';
  }

}
